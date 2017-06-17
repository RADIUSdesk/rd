<?php

App::import('Vendor', 'tcpdf/tcpdf');

class GenericPdf extends TCPDF {

    var $Logo           = 'img/realms/logo.jpg';       //Default Logo
    var $Title          = 'Set The Title';
    var $Language       = 'en';

	public function Header() { 

        $this->SetDrawColor(180,180,180);
        $this->SetTextColor(50,50,50);
        $this->SetLineWidth(0.1);
        $this->SetFont('dejavusans','',10);

        if($this->getRTL()){
            $this->Image(WWW_ROOT.$this->Logo,180,0,10,0,'','','',true);
	     //   $this->Image(WWW_ROOT.DS.$this->Logo,180,0,25,8,'','','');
        }else{
            $this->Image(WWW_ROOT.$this->Logo,10,0,10,0,'','','',true);
           // $this->Image(WWW_ROOT.DS.$this->Logo,10,0,25,8,'','','');
        }
        $this->Cell(0,9,$this->Title,1,0,'C');
        $this->SetFont('dejavusans','',8);

        if($this->getRTL()){
            $this->SetX(150);
            $this->Cell(0,9,date("F j, Y, g:i a"),0,1);
        }else{
            $this->Cell(0,9,date("F j, Y, g:i a"),0,1,'R');
        }

        $this->Ln(10);
	}

	// Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Access Provider Detail
    function addRealm($d)
    {
        
        $font_type_1    = 'dejavusans';
        $font_type_2    = 'dejavusans';
        $font_encode    = 'windows-1252';
        $font_format_b  = 'B';
        $font_format_i  = '';
       
        //===== 2 x Borders =======
        //We start by placing two rounded borders which within we will place the realm info.
        $x_start        = $this->w - 200;   //Page width minus 200 start X position
        $x_txt          = $x_start+5;

        $x_start_mid    = $this->w - 100;   //Middle of page start position
        $x_mid_txt      = $x_start_mid+5;

        $y_start        = 12;               //Start Y position of the borders
        $y_txt          = $y_start+2;
        $width          = 90;               //How wide the borders will be
        $height         = 35;               //Hight of borders
        $radius         = 2.5;              //Radius of corners

        $cell_width     = 100;
        $cell_outline   = 0;     

        //Border starts left side of page
        $this->RoundedRect($x_start,$y_start,$width,$height,$radius,'1111','',
            array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(122, 122, 143)),array());

        //Border starts in middle of page
        $this->RoundedRect($x_start_mid,$y_start,$width,$height,$radius,'1111','',
            array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(122, 122, 143)),array());

        
        //=== LEFT Side =====
        //AP Name
        $this->SetXY($x_txt,$y_txt); //Position the start place
        $this->SetFont($font_type_1,$font_format_b,12);
        $this->Cell($cell_width, 5,$d['name'],$cell_outline,2);  //Name of AP

        //AP Address
        $this->SetFont($font_type_1,$font_format_b,10);
        $this->Cell($cell_width,4,__("Address"),$cell_outline,2);
        $this->SetFont($font_type_2,'',8);
        $address = $d['street_no']." ".$d['street']."\n".$d['town_suburb']."\n".$d['city']."\n".$d["country"]."\nLat ".$d["lat"]."\n"."Lng ".$d["lon"];
        $this->MultiCell($cell_width,3,$address,$cell_outline,2);

        //=== RIGHT Side ===
        //Contact Detail
        $this->SetXY( $x_mid_txt, $y_txt );
        $this->SetFont($font_type_1,$font_format_b,8);
        $this->Cell($cell_width,4,__('Contact Detail'),$cell_outline,2);
        //url
        if($d['url'] != ''){
            $this->SetFont($font_type_2,$font_format_i,8);
           // $this->SetTextColor(0,0,255);
            $this->Cell($cell_width,3,$d['url'],$cell_outline,2);
        }
        //email
        if($d['email'] != ''){
            $this->SetFont($font_type_2,$font_format_i,8);
          //  $this->SetTextColor(0,0,255);
            $this->Cell($cell_width,3,$d['email'],$cell_outline,2);
        }

        $this->SetTextColor(0);

        //phone
        if($d['phone'] != ''){
            $this->SetFont($font_type_2,$font_format_i,8);
            $this->Cell($cell_width,3,$d['phone'].' ('.__('phone').')',$cell_outline,2);
        }

        //cell
        if($d['fax'] != ''){
            $this->SetFont($font_type_2,$font_format_i,8);
            $this->Cell($cell_width,3,$d['fax'].' ('.__('fax').')',$cell_outline,2);
        }

         //fax
        if($d['cell'] != ''){
            $this->SetFont($font_type_2,$font_format_i,8);
            $this->Cell($cell_width,3,$d['fax'].' ('.__('cell').')',$cell_outline,2);
        }
    }


     //This will loop throug the vouchers, creating them
    function addVouchers($vouchers)
    {
        //Initial positioning
        $this->left_col = 1;
        $this->SetY(55);
        $this->Ln();
        foreach($vouchers as $i){
            $this->addVoucher($i);
            $this->left_col = !($this->left_col);
        }
    }

    //Voucher detail window
    function addVoucher($voucher)
    {

        $font_type      = 'dejavusans';
        $font_encode    = 'windows-1252';
        $font_format_b  = 'B';
        $font_format_i  = '';

        $text_size      = 6;    //Up this value to increase the text inside the voucher
        $cell_height    = 3;    //Up this value to increase the space between the lines in the voucher

        //Experiment
        if($this->GetY() > 240){
            $this->AddPage();
        }

        //Get the current pos
        $x_curr = $this->GetX();
        $y_curr = $this->GetY();


        if($this->left_col){
            $r1  = $this->w - 200;
            if($this->getRTL()){
                $border_start  = $this->w - 100;
            }else{
                $border_start = $r1;
            }
        }else{
            $r1  = $this->w - 100;
            if($this->getRTL()){
                $border_start  = $this->w - 200;
            }else{
                $border_start = $r1;
            }
        }

        $r2  = $r1 + 88;
        $y1  = $y_curr;
        $y2  = 25 ;         //Up this value to increase the size of the woucher's frame
       // $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');

        $this->RoundedRect($border_start,$y1,($r2 - $r1),$y2,2.5,'1111','',
        array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(184, 185, 198)),array());


        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 );
        $this->SetFont( $font_type, $font_format_b, 10);

        $this->Cell(10,5, $this->Title, 0, 2, "C");
        $x_p = $this->GetX()-18;

		if($voucher['username'] == $voucher['password']){	//Assume single field

			$this->SetX($x_p);
			$this->SetFont( 'dejavusans','', 8);
			$this->Cell(22,$cell_height, __("Voucher"), 0, 0, "L");

			$this->SetFont( 'dejavusans', $font_format_b, 8);
			$this->Cell(30,$cell_height, $voucher['username'], 0, 2, "L");
		}else{
			$this->SetX($x_p);
			$this->SetFont( 'dejavusans','', 8);
			$this->Cell(22,$cell_height, __("Username"), 0, 0, "L");

			$this->SetFont( 'dejavusans', $font_format_b, 8);
			$this->Cell(30,$cell_height, $voucher['username'], 0, 2, "L");

			//--Password----
			$this->SetFont( 'dejavusans', '', 8);
			$this->SetX($x_p);
			$this->Cell(22,$cell_height,__("Password"), 0, 0, "L");

			$this->SetFont('dejavusans', $font_format_b, 8);
			$this->Cell(30,$cell_height, $voucher['password'], 0, 2, "L");
		}

        //Profile
        $this->SetTextColor(157,157,167);
        $this->SetFont( $font_type, $font_format_i, $text_size);
        $this->SetX($x_p);
        $this->Cell(22,$cell_height,__("Profile") , 0, 0, "L");

        $this->SetFont( $font_type, $font_format_b, $text_size);
        $this->Cell(30,$cell_height, $voucher['profile'], 0, 2, "L");

        //---Duration---
        //Do not print the days_valid if it is not specified....
        
        if($voucher['days_valid'] != ''){

            $this->SetFont( $font_type, $font_format_i, $text_size);
            $this->SetX($x_p);
            $this->Cell(22,$cell_height,__("Valid for") , 0, 0, "L");

            $this->SetFont( $font_type, $font_format_b, $text_size);
            $this->Cell(30,$cell_height, $voucher['days_valid'], 0, 2, "L");
        }

        //---Expiry Date---
        if($voucher['expiration'] != ''){
            $this->SetFont( $font_type, $font_format_i, $text_size);
            $this->SetX($x_p);
            $this->Cell(22,$cell_height,__("Expiry date") , 0, 0, "L");

            $this->SetFont( $font_type, $font_format_b, $text_size);
            $this->Cell(30,$cell_height, $voucher['expiration'], 0, 2, "L");
        }
        //Reset again
        $this->SetTextColor(0,0,0);

        $this->Ln();
        if($this->getRTL()){
            $this->Image(WWW_ROOT.$this->Logo,$r1+18,$y_curr+5,10,0,'','','',true);
            //$this->Image(WWW_ROOT.DS.$this->Logo,$r1+18,$y_curr+5,15,12);
        }else{
            $this->Image(WWW_ROOT.$this->Logo,$r1+3,$y_curr+5,10,0,'','','',true);
            //$this->Image(WWW_ROOT.DS.$this->Logo,$r1+3,$y_curr+5,15,12);
        }

        if(!($this->left_col)){
            $this->SetY( $y_curr+30);   //Up this value to increase the size of the woucher's frame (in relation to the top one)
        }else{
            $this->SetY( $y_curr);
        }
    }
}

?>  

