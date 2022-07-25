<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './../vendor/autoload.php';
// require 'Exception.php';
// require 'PHPMailer.php';
// require 'SMTP.php';


class Email
{

    public function __construct()
    {
        
    }

    public static function sendEmail( $objUser ){
        $mail = new PHPMailer(true);

        try {   
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->SMTPDebug = 0;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            // $mail->Host       = 'smtp1.example.com';                    // Set the SMTP server to send through
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through --> para gmail buscar los smtp para los correos que desees
            //$mail->Host       = 'smtp.live.com';                    // Set the SMTP server to send through --> para correo buscar los smtp para los correos que desees
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = '2016200220@untels.edu.pe';                     // SMTP username
            $mail->Password   = 'Blanca14sulcaNunura';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('2016200220@untels.edu.pe', 'JOYERIA JULY.SAC'); //yo enviooo
            /*$mail->addAddress('erick8rivas@gmail.com', 'Joe User');     // Add a recipient-> aquien envio*/
            $mail->addAddress($objUser->email);               // Name is optional
            /*$mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');*/
            // $pdf = self::factura();
            // $encoded_content = chunk_split(base64_encode(self::factura()));
            // $mail->addStringAttachment( $encoded_content, 'file.pdf');

            // $url = 'http://localhost/sistema/reportes/exFactura.php?id=2';
            // $binary_content = file_get_contents($url);

            // $mail->AddAttachment($url , 'RenamedFile.pdf');
            // $url = 'http://localhost/sistema/reportes/exFactura.php?id=2';

            // You should perform a check to see if the content
            // was actually fetched. Use the === (strict) operator to
            // check $binary_content for false.
            // if ($binary_content === false) {
            //     throw new Exception("Could not fetch remote content from: '$url'");
            // }

            // // $mail must have been created
            // $mail->AddStringAttachment($binary_content, "sales_invoice.pdf", $encoding = 'base64', $type = 'application/pdf');

            self::factura( $objUser->idventa );
            // Attachments --> para enviar archivos imagenes videos
            $mail->addAttachment($objUser->idventa.'.pdf', 'factura.pdf');         // Add attachments
            // $mail->addAttachment('doc1.pdf');         // Add attachments
            // $mail->AddAttachment('http://localhost/sistema/reportes/exFactura.php?id=2');         // Add attachments
        /* $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name*/

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'FACTURA ELECTRÓNICA';
            $mail->Body    = "Hola $objUser->cliente, le saluda <b>JOYERIA JULY.SAC</b><br/><br/> Se le hace presente la factura de su compra.<br/><br/>Gracias por preferirnos.";
            /*$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';*/
            $mail->CharSet = 'UTF-8';
            $mail->send();
            return 'El mensaje se envio correctamente';
        } catch (Exception $e) {
            var_dump( $e );
            return "hubo un error al enviar el mensaje: {$mail->ErrorInfo}";
        }
    }

    public function factura( $id ){
        include "./../reportes/Factura.php";

        //Establecemos los datos de la empresa
        $logo = "./../reportes/logo.jpg";
        $ext_logo = "jpg";
        $empresa = "Soluciones Innovadoras Perú S.A.C.";
        $documento = "20477157772";
        $direccion = "Chongoyape, José Gálvez 1368";
        $telefono = "931742904";
        $email = "jcarlos.ad7@gmail.com";

        //Obtenemos los datos de la cabecera de la venta actual
        require_once "./../modelos/Venta.php";
        $venta= new Venta();
        // $rsptav = $venta->ventacabecera($_GET["id"]);
        $rsptav = $venta->ventacabecera(2);
        //Recorremos todos los valores obtenidos
        $regv = $rsptav->fetch_object();

        //Establecemos la configuración de la factura
        $pdf = new PDF_Invoice( 'P', 'mm', 'A4' );
        $pdf->AddPage();

        //Enviamos los datos de la empresa al método addSociete de la clase Factura
        $pdf->addSociete(utf8_decode($empresa),
                        $documento."\n" .
                        utf8_decode("Dirección: ").utf8_decode($direccion)."\n".
                        utf8_decode("Teléfono: ").$telefono."\n" .
                        "Email : ".$email,$logo,$ext_logo);
        $pdf->fact_dev( "$regv->tipo_comprobante ", "$regv->serie_comprobante-$regv->num_comprobante" );
        $pdf->temporaire( "" );
        $pdf->addDate( $regv->fecha);

        //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
        $pdf->addClientAdresse(utf8_decode($regv->cliente),"Domicilio: ".utf8_decode($regv->direccion),$regv->tipo_documento.": ".$regv->num_documento,"Email: ".$regv->email,"Telefono: ".$regv->telefono);

        //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
        $cols=array( "CODIGO"=>23,
                    "DESCRIPCION"=>78,
                    "CANTIDAD"=>22,
                    "P.U."=>25,
                    "DSCTO"=>20,
                    "SUBTOTAL"=>22);
        $pdf->addCols( $cols);
        $cols=array( "CODIGO"=>"L",
                    "DESCRIPCION"=>"L",
                    "CANTIDAD"=>"C",
                    "P.U."=>"R",
                    "DSCTO" =>"R",
                    "SUBTOTAL"=>"C");
        $pdf->addLineFormat( $cols);
        $pdf->addLineFormat($cols);
        //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
        $y= 89;

        //Obtenemos todos los detalles de la venta actual
        $rsptad = $venta->ventadetalle($_GET["id"]);

        while ($regd = $rsptad->fetch_object()) {
        $line = array( "CODIGO"=> "$regd->codigo",
                        "DESCRIPCION"=> utf8_decode("$regd->articulo"),
                        "CANTIDAD"=> "$regd->cantidad",
                        "P.U."=> "$regd->precio_venta",
                        "DSCTO" => "$regd->descuento",
                        "SUBTOTAL"=> "$regd->subtotal");
                    $size = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
        }

        //Convertimos el total en letras
        require_once "./../reportes/Letras.php";
        $V=new EnLetras(); 
        $con_letra=strtoupper($V->ValorEnLetras($regv->total_venta,"NUEVOS SOLES"));
        $pdf->addCadreTVAs("---".$con_letra);

        //Mostramos el impuesto
        $pdf->addTVAs( $regv->impuesto, $regv->total_venta,"S/ ");
        $pdf->addCadreEurosFrancs("IGV"." $regv->impuesto %");
        // $pdf->Output('Reporte de Venta','I');
        // $pdf->Output('F', './files/'.$id.'.pdf');
        $pdf->Output('F', './'.$id.'.pdf');

        // return $pdf;
    }
    
}
