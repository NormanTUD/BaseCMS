<?php
	if(!isset($setup_mode)) {
		$setup_mode = 0; // Im setup-Modus werden keine Anfragen ausgeführt. Setupmodus deaktiviert.
	}
	include_once("functions.php");

	if($GLOBALS['reload_page']) {
		header("Refresh:0");
	}

	if(!$page_title) {
		$page_title = 'BaseCMS';
	}
?>
<html>
	<head>
		<!-- Hey, wenn du die Daten dieser Seite brauchst, dann guck doch einfach in die API! Dann brauchst du hier nicht versuchen, HTML mit Regexen zu parsen... -->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />

		<meta http-equiv="X-WebKit-CSP" content="default-src 'self'; script-src 'self'">

		<meta name="description" content="BaseCMS">
		<meta name="keywords" content="BaseCMS">
		<meta name="author" content="Norman Koch">
<?php
		if(preg_match('/index/', basename($_SERVER['SCRIPT_NAME']))) {
?>
			<title><?php
				print htmlentities($page_title);
				$chosen_page_id = get_get('page');
				if(!$chosen_page_id) {
					$chosen_page_id = get_get('show_items');
				}
				if($chosen_page_id) {
					if(check_page_rights($chosen_page_id, 0)) {
						$father_page = get_father_page($chosen_page_id);
						if($father_page) {
							print " | ".get_page_name_by_id($father_page);
						}

						$this_page_title = get_page_name_by_id($chosen_page_id);
						if($this_page_title) {
							print " | ".$this_page_title;
						}
					} else {
						print " &mdash; Kein Zugriff auf diese Seite";
					}
				}
			?></title>
<?php
		} else {
?>
			<title><?php print htmlentities($page_title); ?></title>
<?php
		}
?>
		<meta charset="UTF-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8">

		<link rel="stylesheet" href="data/jquery-ui.css">
		<link rel="stylesheet" href="data/style.css">
		<script src="data/jquery-3.7.0.js"></script>
		<script src="data/jquery-ui.js"></script>
		<script type="text/javascript">
			function reset_comment_box_size (element) {
				element.style.width = '130px';
			}

			function jump(h){
				var url = location.href;               //Save down the URL without hash.
				location.href = "#"+h;                 //Go to the target element.
				history.replaceState(null,null,url);   //Don't like hashes. Changing it back.
			}

			function resizable (el, factor) {
				  var int = Number(factor) || 7.7;
				  function resize() {
					  el.style.width = ((el.value.length + 1) * int) + 'px';
				  }
				  var e = 'keyup,keypress,focus,blur,change'.split(',');
				  for (var i in e) el.addEventListener(e[i],resize,false);
				  resize();
			}

			function resize_comment_box (box) {
				//console.log(box)
				resizable(box, 10);
			}

			function termin_verschieben (jahr, monat, id) {
				alert("jahr: " + jahr + ", monat: " + monat + ", id: " + id);
			}
		</script>
<?php
		if($GLOBALS['logged_in_user_id']) {
?>
			<script type="text/javascript">
				$(function() {
					$('.datepicker').datepicker({
						prevText: '&#x3c;zurück', prevStatus: '',
							prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
							nextText: 'Vor&#x3e;', nextStatus: '',
							nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
							currentText: 'heute', currentStatus: '',
							todayText: 'heute', todayStatus: '',
							clearText: '-', clearStatus: '',
							closeText: 'schließen', closeStatus: '',
							monthNames: ['Januar','Februar','März','April','Mai','Juni',
							'Juli','August','September','Oktober','November','Dezember'],
							monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
							'Jul','Aug','Sep','Okt','Nov','Dez'],
							dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
							dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
							dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
							showMonthAfterYear: false,
							showOn: 'both',
							buttonImageOnly: false,
							dateFormat:'dd.mm.yy'
							//dateFormat:'yy-mm-dd' // old us
							//dateFormat:'dd-mm-yy'
					});

					$('.monthpicker').datepicker( {
						changeMonth: true,
						changeYear: true,
						showButtonPanel: true,
						dateFormat: 'MM yy',
						onClose: function(dateText, inst) { 
							$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
						}
					});
				});

				function unwichtigesausblenden () {
					$('.unwichtig').toggle();
				}
<?php
				if(get_get('goto_line')) {
?>
					$(document).ready(function() {
						jump('<?php print(htmlentities(get_get('goto_line'))); ?>');
					});
<?php
				}
?>
			</script>
<?php
		}
?>
	</head>
<body>
