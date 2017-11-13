<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";


  if (Functions::wasRequestPost()) {
    try {
      $data = array(
        "issue_date" => strtotime(Functions::getPostVarSafely("issue_date")),
        "due_date" => strtotime(Functions::getPostVarSafely("due_date")),
        "technician" => Functions::getPostVarSafely("technician"),
        "total_price" => money_format("%.2n", floatval(Functions::getPostVarSafely("price"))),
        "work_description" => Functions::getPostVarSafely("work_description"),
        "state" => Functions::getPostVarSafely("state"),
      );
      $invoice = new Invoice(Functions::getPostVarSafely("invoice_id"));
      $invoice->update($data);
      $rend->setAlert("success", "Updated invoice #" . $invoice->id . " successfully.");
    } catch (Throwable $e) {
      $rend->setAlert("danger", "Failed updating invoice \"" . Functions::getPostVarSafely("invoice_id") . "\". Error: " . $e->getMessage());
    }
  } else {
    if (isset($_GET["download_invoice"])) {
      $invoice_id = $_GET["download_invoice"];
      if (!Invoice::exists($invoice_id)) {
        $rend->setAlert("danger", "Invoice " . $invoice_id . " isn't available for download.");
      } else {
        $invoice = new Invoice($invoice_id);
        $client = new Client($invoice->client);
        $billto = ($client->name . "\n\n" . $client->address);
        $data = array(
          "bill_to" => $billto,
          "invoice_id" => $invoice->id,
          "issue_date" => date("d/m/Y", $invoice->issue_date),
          "due_date" => date("d/m/Y", $invoice->due_date),
          "technician" => $invoice->technician,
          "work_description" => htmlspecialchars_decode($invoice->work_description),
          "total" => ("$" . money_format("%.2n", floatval($invoice->price))),
        );
        $pdf = new PDF($invoice->state, $data);
        $pdf->flatten();
        $pdf->download($invoice->id);
      }
    }

    if (isset($_GET["email_invoice"])) {
      $invoice_id = $_GET["email_invoice"];
      if (!Invoice::exists($invoice_id)) {
        $rend->setAlert("danger", "Invoice " . $invoice_id . " isn't available for emailing. The invoice does not exist in the database.");
      } else {
        $invoice = new Invoice($invoice_id);
        $client = new Client($invoice->client);
        if (!$client->email || empty($client->email)) {
          $rend->setAlert("warning", $client->name . " hasn't got an email address registered in their client record.");
        } else {
          $names = explode(" ", $client->contact);
          $client->first_name = $names[0];
          $billto = ($client->name . "\n\n" . $client->address);
          $data = array(
            "bill_to" => $billto,
            "invoice_id" => $invoice->id,
            "issue_date" => date("d/m/Y", $invoice->issue_date),
            "due_date" => date("d/m/Y", $invoice->due_date),
            "technician" => $invoice->technician,
            "work_description" => htmlspecialchars_decode($invoice->work_description),
            "total" => ("$" . money_format("%.2n", floatval($invoice->price))),
          );
          $pdf = new PDF($invoice->state, $data);
          $pdf->flatten()
              ->save("/tmp/TomTecSolutions-{$invoice->id}.pdf");

          $emailRenderer = new Renderer;
          $emailRenderer->setTemplateVariable("Client", $client);
          $emailRenderer->setTemplateVariable("Invoice", $invoice);

          $email = new Email;
          $email->Subject = "Invoice #{$invoice->id}";
          $email->MsgHTML($emailRenderer->render("emailinvoice.tmpl", true));
          $email->AddAddress($client->email, $client->contact);
          $email->AddAttachment("/tmp/TomTecSolutions-{$invoice->id}.pdf");

          if(!$email->Send()) {
            $rend->setAlert("danger", "Error while emailing invoice: " . $email->ErrorInfo);
          } else {
            $rend->setAlert("success", "Sent email to " . $client->email . ".");
            unlink("/tmp/TomTecSolutions-{$invoice->id}.pdf"); // remove temporary file
          }
        }
      }
    }

    if (isset($_GET["sms_invoice"])) {
      $invoice_id = $_GET["sms_invoice"];
      if (!Invoice::exists($invoice_id)) {
        $rend->setAlert("danger", "Invoice " . $invoice_id . " isn't available for notifications because it does not exist in the database.");
      } else {
        $invoice = new Invoice($invoice_id);
        $client = new Client($invoice->client);
        $phone1 = str_replace(" ", "", $client->phone_primary);
        $phone2 = str_replace(" ", "", $client->phone_secondary);
        if (substr($phone1, 0, 2) === "04") {
          $phone = $phone1;
        } elseif (substr($phone2, 0, 2) === "04") {
          $phone = $phone2;
        } else {
          $rend->setAlert("danger", $client->name . " does not have a mobile number listed.");
        }
        if (isset($phone)) {
          $sms = new SMS($phone);
          $names = explode(" ", $client->contact);
          $client->first_name = $names[0];
          if ($invoice->state == "Awaiting Payment") {
            $sms->setMessage("Hi " . $client->first_name . ",\n\nInvoice #" . $invoice->id . " for $" . money_format("%.2n", floatval($invoice->price)) . " is now awaiting payment, and has been emailed to you. It's due by " . date("d/m/Y", $invoice->due_date) . ".\n\nThanks,\nTomTec Solutions");
          } elseif ($invoice->state == "Overdue") {
            $sms->setMessage("Hi " . $client->first_name . ",\n\nInvoice #" . $invoice->id . " for $" . money_format("%.2n", floatval($invoice->price)) . " is overdue. " . strtoupper("Please pay as soon as possible.") . "\n\nThanks,\nTomTec Solutions");
          } elseif ($invoice->state == "Paid") {
            $sms->setMessage("Hi " . $client->first_name . ",\n\nInvoice #" . $invoice->id . " for $" . money_format("%.2n", floatval($invoice->price)) . " is now marked as paid.\n\nThanks,\nTomTec Solutions");
          }
          try {
            if (intval(SMS::getCreditBalance()) < 1) {
              $rend->setAlert("danger", "There is no credit left in your SMS Broadcast account. <a href=\"https://www.smsbroadcast.com.au/\">Click here to go to SMS Broadcast</a>.");
            } else {
              $sms->send();
              $rend->setAlert("success", "Message sent to " . $client->name . " successfully.");
            }
          } catch (Throwable $e) {
            $rend->setAlert("warning", "An error occurred. Error: " . $e->getMessage());
          }
        }
      }
    }

    if (isset($_GET["delete"])) {
      if (Invoice::exists($_GET["delete"])) {
        $invoice = new Invoice($_GET["delete"]);
        if ($invoice->delete()) {
          $rend->setAlert("success", "Deleted invoice #" . $invoice->id . " successfully.");
        } else {
          $rend->setAlert("danger", "Failed to delete invoice #" . $invoice->id . ".");
        }
      } else {
        $rend->setAlert("warning", "Failed to delete invoice #" . $_GET["delete"] . ", because it does not exist.");
      }
    }
  }

  try {
    $invoices = Invoice::allInvoices();
    foreach ($invoices as &$invoice) {
      $invoice->ClientData = new Client($invoice->client);
      $invoice->issue_date_datesafe = date("Y-m-d", $invoice->issue_date);
      $invoice->due_date_datesafe = date("Y-m-d", $invoice->due_date);
      $invoice->issue_date_humanreadable = date("d/m/Y", $invoice->issue_date);
      $invoice->due_date_humanreadable = date("d/m/Y", $invoice->due_date);
      if (time() > $invoice->due_date) {
        $invoice->pastdue = true;
      } else {
        $invoice->pastdue = false;
      }
      $invoice->total_price = money_format("%.2n", floatval($invoice->price));
    }
    $rend->setTemplateVariable("Invoices", $invoices);
  } catch (Throwable $e) {
    $rend->setAlert("warning", "Could not get invoice list. Perhaps there are no invoices in the database. Error: " . $e->getMessage());
  }
