<?php
require('../../../../../../wp-load.php');
$wp->init(); $wp->parse_request(); $wp->query_posts();
$wp->register_globals(); $wp->send_headers();
if(isset($_POST['invoice'])){
	$content = stripslashes($_POST['invoice']);
	// MPDF //
	/*
	require_once('./MPDF54/mpdf.php');
	
	$mpdf = new mPDF('utf-8',    // mode - default ''
	 'A4',    // format - A4, for example, default ''
	 10,     // font size - default 0
	 'trebuchetms',    // default font family
	 15,    // margin_left
	 15,    // margin right
	 16,     // margin top
	 16,    // margin bottom
 	 9,     // margin header
	 9,     // margin footer
	 'L');  // L - landscape, P - portrait
	$explode = explode('</style>', $content);
	$mpdf->WriteHTML(str_replace('<style>','', $explode[0]), 1);
	$mpdf->WriteHTML($explode[1], 2);
	$mpdf->Output();
	
	// html2pdf //
	*/
	require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');
	try {
		$html2pdf = new HTML2PDF('P', 'A4', 'en');
		$html2pdf->pdf->SetDisplayMode('real');
		$html2pdf->pdf->setImageScale(0.57);
		$html2pdf->setDefaultFont('helvetica');
		$html2pdf->pdf->SetProtection(array('print'), '', 'admin_password');
		$html2pdf->pdf->cropMark(50, 70, 10, 10, 'TL', array(0,0,0));
		$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		if($_POST['invoice_preview'] == 0){
			$invoices = get_option('reservations_invoice_number');
			if(!is_array($invoices)) $invoices = array('id' => array(), 'nr' => 1);
			if(!isset($invoices['id']) || !is_array($invoices['id'])) $invoices['id'] = array();
			if(!in_array($_POST['id'], $invoices['id'])){
				$invoices['id'][] = $_POST['id'];
				$invoices['nr'] = $invoices['nr'] +1;
				update_option('reservations_invoice_number', $invoices);
			}
			$filename = $_POST['filename'].'.pdf';

			$html2pdf->Output($filename, 'F');

			Header("Content-type: application/pdf");
			Header("Content-Disposition: attachment; filename=$filename");
			readfile("$filename");
			unlink($filename);
		}
		else $html2pdf->Output($_POST['filename'].'.pdf');
	} catch(HTML2PDF_exception $e) {
		echo $e;
		exit;
	}
}
?>