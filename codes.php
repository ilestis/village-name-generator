<?php
$file = 'postcodes.json';
$string = file_get_contents($file);
$json = json_decode($string, true);

echo '<p>Only 
	<a href="?">All</a> 
	<a href="?country=England">England</a> 
	<a href="?country=Scotland">Scotland</a> 
	<a href="?country=Wales">Wales</a> 
	<a href="?country=Northern Ireland">Northern Ireland</a>
</p>';

// Stats
$startStats = [];
$lengthStats = [];
$nextStats = [];
$towns = [];

$stats = [
	'composed' => 0,
	'total' => 0,
];

$country = !empty($_GET['country']) ? $_GET['country'] : null;

// Parse data
foreach($json as $town) {
	$name = strtolower($town['town']);
	$length = strlen($name);
	
	if ($length > 0) {
		if (!empty($country)) {
			if ($country == $town['country_string']) {
				$towns[] = $name;
			}
		} else {
			$towns[] = $name;
		}
	}
}

foreach ($towns as $town) {
	$name = strtolower($town);
	
	$starts = $name[0];
	$length = strlen($town);
	$composed = strpos($town, '-') !== false;
		
	// Starting letter
	if (!isset($startStats[$starts])) {
		$startStats[$starts] = [
			'count' => 0,
			'percentage' => 0,
			'percentage2' => 0
		];
	}
	$startStats[$starts]['count']++;
	
	// Length
	if (!isset($lengthStats[$length])) {
		$lengthStats[$length] = [
			'count' => 0,
			'percentage' => 0,
			'percentage2' => 0,
		];
	}
	$lengthStats[$length]['count']++;
	
	// Composed
	if ($composed) {
		$stats['composed']++;
	}
	
	$stats['total']++;
	
	// Calc next letter stats
	for ($i = 0; $i < ($length-1); $i++) {
		$l = strtolower($town[$i]);
		$n = strtolower($town[$i+1]);
			
		if (!isset($nextStats[$l])) {
			$nextStats[$l] = ['total' => 0];
		}
		
		if (!isset($nextStats[$l][$n])) {
			$nextStats[$l][$n] = ['count' => 0, 'percentage' => 0, 'percentage2' => 0];
		}
		$nextStats[$l][$n]['count']++;
		$nextStats[$l]['total']++;
	}
		
}

// Sort
uasort($startStats, function($a, $b) {
	return $a['count'] - $b['count'];
});
uasort($lengthStats, function($a, $b) {
	return $a['count'] - $b['count'];
});

// Calc percentages
$totalPercentage = 0;
foreach ($startStats as $letter => $data) {
	$percentage = round(($data['count']/$stats['total']) * 100, 2);
	$totalPercentage+= $percentage;
	
	$startStats[$letter]['percentage'] = $percentage;
	$startStats[$letter]['percentage2'] = $totalPercentage;
}

$totalPercentage = 0;
foreach ($lengthStats as $length => $data) {
	$percentage = round(($data['count']/$stats['total']) * 100, 2);
	$totalPercentage+= $percentage;
	
	$lengthStats[$length]['percentage'] = $percentage;
	$lengthStats[$length]['percentage2'] = $totalPercentage;
}

foreach ($nextStats as $letter => $data) {
	$totalPercentage = 0;
	foreach ($data as $l => $d) {
		
		if ($l == 'total') {
			continue;
		}
		
		$percentage = round(($d['count'] / $data['total']) * 100, 2);
		$totalPercentage += $percentage;
		
		$nextStats[$letter][$l]['percentage'] = $percentage;
		$nextStats[$letter][$l]['percentage2'] = $totalPercentage;
	}
}

/**
 * Calc a random length
 */
 
$lengthR = mt_rand(1, 9999)/ 100;
$startR = mt_rand(1, 9999)/ 100;

$length = getRandom($lengthR, $lengthStats);
$first = getRandom($startR, $startStats);
list($name, $debug) = getRandomName($first, $length, $nextStats);

echo "<h1>Random</h1>";
echo "Name Length ($lengthR): $length<br />";
echo "First Letter ($startR): $first<br />";
echo "Random name: $name<br />";
echo '<i>Itteration: ' . $debug . '</i><br />';

?>

<h1>Analysis</h1>

<p>List provided by <a href="https://github.com/Gibbs/uk-postcodes" target="_blank">Gibbs/uk-postcodes</a>.</p>

<div style="float: left; margin: 10px; padding: 5px; background-color: #efefef;">
	<h2>Starting Letter</h2>
	<table>
	<tr>
		<thead>
			<th>Letter</th>
			<th>Count</th>
			<th>%</th>
			<th>Incrementing %</th>
		</thead>
	</tr>
	<tbody>
	<?php foreach ($startStats as $letter => $data): ?>
		<tr>
			<td><?=$letter?></td>
			<td><?=$data['count']?></td>
			<td><?=$data['percentage']?></td>
			<td><?=$data['percentage2']?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
</div>

<div style="float: left; margin: 10px; padding: 5px; background-color: #efefef;">
	<h2>Name Length</h2>
	<table>
	<tr>
		<thead>
			<th>Length</th>
			<th>Count</th>
			<th>%</th>
			<th>Incrementing %</th>
		</thead>
	</tr>
	<tbody>
	<?php foreach ($lengthStats as $length => $data): ?>
		<tr>
			<td><?=$length?></td>
			<td><?=$data['count']?></td>
			<td><?=$data['percentage']?></td>
			<td><?=$data['percentage2']?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
</div>

<div style="float: left; margin: 10px; padding: 5px; background-color: #efefef;">
	<h2>Next Letter Probability</h2>
	<table>
	<tr>
		<thead>
			<th>Letter</th>
			<th>Count</th>
			<th>%</th>
			<th>Incrementing %</th>
		</thead>
	</tr>
	<tbody>
	<?php foreach ($nextStats as $letter => $data): ?>
		<tr style="background-color: #aaa;">
			<td colspan="4"><?=$letter?></td>
		</tr>
		<?php foreach ($data as $l => $d):
			if ($l == 'total') continue; ?>
		<tr>
			<td>- <?=$l?></td>
			<td><?=$d['count']?></td>
			<td><?=$d['percentage']?></td>
			<td><?=$d['percentage2']?></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td> </td>
			<td colspan="3"><?=$data['total']?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
</div>
<br style="clear: both;">
<?php


echo '<h2>Towns</h2>';
foreach ($towns as $town) {
	echo $town.', ';
}

function getRandomName($first, $length, $stats) {
	$name = strtoupper($first);
	$previous = strtolower($first);
	$debug = '';
	for ($i = 0; $i < $length; $i++) {
		$r = mt_rand(1, 9999) / 100;
		$debug .= $r.', ';
		
		/*echo "Looking up data for $previous with $r<br>";
		echo '<pre>';
		var_dump($stats[$previous]);
		echo '</pre>';*/
		$next = getRandom($r, $stats[$previous]);
		$name .= $next;
		
		$previous = $next;
	}
	
	return [$name, $debug];
}	
 
function getRandom($p, $data) {
	$last = null;
	foreach ($data as $key => $values) {
		$last = $key;
		if ($p <= $values['percentage2']) {
			return $key;
		}
	}
	return $key;
}