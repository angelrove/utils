<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 * Envía un email en formato HTML.
 *   - Elimina la codificación UTF8.
 *   - Ejemplo de $from = 'Galletas Fontaneda <gfontaneda@gmail.com>';
 */

namespace angelrove\utils;


class SendMail
{
   private $headers = array();

   //------------------------------------------------------------------
   function __construct($from, $mailto, $subject, $body)
   {
      $this->from    = utf8_decode($from);
      $this->mailto  = $mailto;
      $this->subject = utf8_decode($subject);
      $this->body    = utf8_decode($body);

      // Headers ----------
      $this->headers[] = 'From: '.$this->from;
      $this->headers[] = 'X-Mailer: PHP/'. phpversion();
      $this->headers[] = 'Mime-Version: 1.0';
      $this->headers[] = 'Content-Type: text/html; charset=iso-8859-1';
   }
   //------------------------------------------------------------------
   function set_bcc($bcc)
   {
      $this->headers[] = 'Bcc: '.$bcc;
   }
   //------------------------------------------------------------------
   function set_replyTo($replyTo)
   {
      $this->headers[] = 'Reply-To: '.$replyTo;
      $this->headers[] = 'Return-Path: '.$replyTo;
   }
   //------------------------------------------------------------------
   function send()
   {
      // Body -------------
      $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
       <html>
       <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style>
        body {
        background=#fff;
         font-family: Verdana, Arial;
         line-height: 21px;
        }
       </style>
       </head>
       <body>
        '.$this->body.'
       </body>
       </html>';

      // Mail -------------
      if(IS_LOCALHOST) {
        print_r2("
<b>SendMail() >> IS_LOCALHOST</b>

//-- Headers ------------------------
".htmlentities(implode("\r\n", $this->headers))."
//-----------------------------------
");

        print_r2("
          From:    '".htmlentities($this->from)."'
          To:      '$this->mailto'
          Subject: '$this->subject'
        ");

        print_r2($this->body);
        return;
        // exit;
      }

      mail($this->mailto, $this->subject, $body, implode("\r\n", $this->headers));
   }
   //------------------------------------------------------------------

  }
