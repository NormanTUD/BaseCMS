		<div style="width: 100%; text-align: center; margin-top: 20px;">
<?php
	$this_page_file = ($_SERVER['REQUEST_URI']);
	if(preg_match('/\/(\?.*)?$/', $this_page_file)) {
		$this_page_file = 'index.php';

	}
	$this_page_file = basename($this_page_file);
	$this_page_file = preg_replace('/\?.*/', '', $this_page_file);

	$sites = array(
		'index.php' => 'Kundendaten'
	);
?>
	<i>
<?php
	$c = 0;
	if(count($sites) > 1) {
		foreach ($sites as $url => $name) {
			if(!($url == 'faq.php' && !faq_has_entry())) {
				if($url == $this_page_file) {
	?>
					<b><a href="<?php print $url; ?>"><?php print htmlentities($name); ?></a></b>
	<?php
				} else {
	?>
					<a href="<?php print $url; ?>"><?php print htmlentities($name); ?></a>
	<?php
				}
				$c++;
				if($c != count($sites)) {
					print " / ";
				}
			} else {
				$c++;
			}
		}
	}
?>
	</i>
	<br />
	<br />
	&copy; <?php
			$thisyear = date('Y');
			$startyear = 2018;
			if($thisyear == $startyear) {
				print date('Y');
			} else if($thisyear <= date('Y')) {
				print "$startyear&nbsp;&mdash;&nbsp;$thisyear";
			} else {
				print "$startyear &mdash;<span class='class_red'>An die Administratoren: Falsch eingestellte Server-Zeit. Bitte überprüfen.</span> &mdash; ";
			}

			if(date('j') == 10 && date('m') == 8 || get_get('geburtstag')) {
				$alter = $thisyear - 1993;
				//$params = array_merge($_GET, array("sende_geburtstagsgruss" => "1"));
				//$url = $_SERVER['REQUEST_URI'].http_build_query($params);
				//print " <a href='$url' title='Geburtstagsgruß senden'>&#x1F382; frohen $alter. Geburtstag, </a>";
				print " &#x1F382; frohen $alter. Geburtstag, ";
			}
		?> Norman Koch
	</div>

<?php
	include('query_analyzer.php');

	if($GLOBALS['end_html']) {
?>
	<script type="text/javascript">
		function colorHashMe () {
			$(".colorhashme").each(function () {
				var input = this;
				var colorHash = new ColorHash();
				var str = input.innerHTML;
				var hex = colorHash.hex(str);
				input.innerHTML = '<span class="hexcolored" style="color: ' + hex.toUpperCase() + ' !important;">' + str + '</span>';
			});
		}
		$(document).ready(function() {
			colorHashMe();
		});
	</script>
	</body>
</html>
<?php
	}
?>
