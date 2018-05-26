<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
/*
Plugin Name: Download CSV
Author: Mike Schinkel
Author URI: http://mikeschinkel.com
 */
add_action('plugins_loaded','check_for_download');
add_action('plugins_loaded','activate');

function activate() {
    $role = get_role('administrator');
    if (!array_key_exists('download_cf_report', $role->capabilities))
    {
        $role->add_cap('download_cf_report');
    }
}
function check_for_download() {
    global $pagenow;
    if ($pagenow=='admin.php' && 
        current_user_can('download_cf_report') && 
        isset($_GET['download'])  && 
        $_GET['download']=='cf550yfirlit.xml') 
    {
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=cf550yfirlit.xml");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo generate_excel_report();
        exit();
    }
}

function generate_excel_report() {
    global $wpdb;
    $attendances = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_attendance".' ORDER BY id desc');
    $members = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_members".' ORDER BY name asc');
    $programs = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_programs".' ORDER BY date desc');
    $purchases = $wpdb->get_results('SELECT p.member_ssn as member_ssn, p.subscription_id as subscription_id, p.date as date, p.id as id, m.name as member_name, s.name as subscription_name FROM ' . $wpdb->prefix.'cf_purchase p LEFT JOIN '.$wpdb->prefix.'cf_members m ON p.member_ssn=m.ssn LEFT JOIN '.$wpdb->prefix.'cf_subscription s ON p.subscription_id=s.id ORDER BY p.id desc');
    $subscriptions = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_subscription".' ORDER BY id desc');
    
echo '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Crossfit550</Author>
  <LastAuthor>Crossfit550</LastAuthor>
  <Created>2017-09-26T20:47:05Z</Created>
  <LastSaved>2017-09-26T22:24:07Z</LastSaved>
  <Version>16.00</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <AllowPNG/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8430</WindowHeight>
  <WindowWidth>21570</WindowWidth>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>0</WindowTopY>
  <ActiveSheet>0</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s62">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s63">
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s64">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="s65">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s66">
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s67">
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s68">
   <NumberFormat ss:Format="[$-F400]h:mm:ss\ AM/PM"/>
  </Style>
  <Style ss:ID="s69">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Color="#000000"
    ss:Bold="1"/>
   <NumberFormat/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Iðkendur">
  <Table ss:ExpandedColumnCount="3" ss:ExpandedRowCount="'.(count($members)+1).'" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="176.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="79.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="78"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:StyleID="s62"><Data ss:Type="String">Nafn</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Kennitala</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Stofnaður</Data></Cell>
   </Row>';
   foreach($members as $m) {
        echo
            '<Row ss:AutoFitHeight="0">
                <Cell><Data ss:Type="String">'.htmlspecialchars($m->name).'</Data></Cell>
                <Cell><Data ss:Type="String">'.htmlspecialchars($m->ssn).'</Data></Cell>
                <Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.$m->created.'T00:00:00.000</Data></Cell>
            </Row>';
   }
   echo '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>-3</HorizontalResolution>
    <VerticalResolution>-3</VerticalResolution>
   </Print>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Æfingar">
  <Table ss:ExpandedColumnCount="7" ss:ExpandedRowCount="'.(count($programs)+1).'" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="33"/>
   <Column ss:Width="79.5"/>
   <Column ss:Width="66"/>
   <Column ss:StyleID="s64" ss:AutoFitWidth="0" ss:Width="316.5"/>
   <Column ss:Width="54"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:StyleID="s62"><Data ss:Type="String">Nr</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Dagsetning</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Titill</Data></Cell>
    <Cell ss:StyleID="s65"><Data ss:Type="String">Lýsing</Data></Cell>
    <Cell ss:StyleID="s66"/>
    <Cell ss:StyleID="s67"/>
    <Cell ss:StyleID="s67"/>
   </Row>
   ';
   foreach($programs as $p) {
    echo'<Row ss:AutoFitHeight="0">
        <Cell><Data ss:Type="Number">'.$p->id.'</Data></Cell>
        <Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.($p->date==null?'':$p->date.'T00:00:00.000').'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($p->title).'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($p->description).'</Data></Cell>
        <Cell ss:StyleID="s67"/>
        <Cell ss:StyleID="s67"/>
        <Cell ss:StyleID="s67"/>
    </Row>'; 
   }
   echo '
   
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>12</ActiveRow>
     <ActiveCol>3</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Mætingar">
  <Table ss:ExpandedColumnCount="6" ss:ExpandedRowCount="'.(count($attendances)+1).'" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="79.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="76.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="86.25"/>
   <Column ss:StyleID="s68" ss:AutoFitWidth="0" ss:Width="57.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="78"/>
   <Column ss:AutoFitWidth="0" ss:Width="54.75"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:StyleID="s62"><Data ss:Type="String">ÆfingarNr</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Dagur</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Dagsetning</Data></Cell>
    <Cell ss:StyleID="s69"><Data ss:Type="String">Tími</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Kennitala</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Skor</Data></Cell>
   </Row>';
   foreach ($attendances as $a) {
    echo '
    <Row ss:AutoFitHeight="0">
    <Cell><Data ss:Type="Number">'.$a->id.'</Data></Cell>
    <Cell><Data ss:Type="String">'.htmlspecialchars($a->day).'</Data></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.($a->date==null?'':$a->date.'T00:00:00.000').'</Data></Cell>
    <Cell><Data ss:Type="DateTime">1899-12-31T'.$a->time.'.000</Data></Cell>
    <Cell><Data ss:Type="String">'.htmlspecialchars($a->member_ssn).'</Data></Cell>
   </Row>';    
   }
   echo '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>16</ActiveRow>
     <ActiveCol>3</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Kort">
  <Table ss:ExpandedColumnCount="5" ss:ExpandedRowCount="'.(count($subscriptions)+1).'" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:Index="2" ss:Width="97.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="69"/>
   <Column ss:AutoFitWidth="0" ss:Width="65.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="67.5"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:StyleID="s62"><Data ss:Type="String">Nr</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Nafn</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Tegund</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Gildi</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Crossfit</Data></Cell>
   </Row>
   ';
   foreach ($subscriptions as $s) {
    echo '<Row ss:AutoFitHeight="0">
        <Cell><Data ss:Type="Number">'.$s->id.'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($s->name).'</Data></Cell>
        <Cell><Data ss:Type="String">'.type_en_to_is($s->type).'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($s->value).'</Data></Cell>
        <Cell><Data ss:Type="String">'.bool_to_string($s->crossfit).'</Data></Cell>
    </Row>';
   }
   echo '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Kaup">
  <Table ss:ExpandedColumnCount="5" ss:ExpandedRowCount="'.(count($purchases)+1).'" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="79.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="131.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="66"/>
   <Column ss:AutoFitWidth="0" ss:Width="86.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="130.5"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:StyleID="s62"><Data ss:Type="String">Kennitala</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Nafn</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">KortaNr</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Korta nafn</Data></Cell>
    <Cell ss:StyleID="s62"><Data ss:Type="String">Dagsetning kaupa</Data></Cell>
   </Row>
   ';
   foreach ($purchases as $p) {
       echo '
       <Row ss:AutoFitHeight="0">
        <Cell><Data ss:Type="String">'.$p->member_ssn.'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($p->member_name).'</Data></Cell>
        <Cell><Data ss:Type="Number">'.$p->subscription_id.'</Data></Cell>
        <Cell><Data ss:Type="String">'.htmlspecialchars($p->subscription_name).'</Data></Cell>
        <Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.($p->date==null?'':$p->date.'T00:00:00.000').'</Data></Cell>
       </Row>';
   }
   echo '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>-3</HorizontalResolution>
    <VerticalResolution>-3</VerticalResolution>
   </Print>
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>9</ActiveRow>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
    ';
}

?>