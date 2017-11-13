<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if (!Authentication::isLoggedIn()) {
    header("HTTP/1.0 403 Forbidden");
    die();
  }

  if (isset($_POST["invoice_id"]) || !empty($_POST["invoice_id"])) {
    $invoice_id = Functions::getPostVarSafely("invoice_id");
    if (!Invoice::exists($invoice_id)) {
      die("Invalid Invoice ID: " . $invoice_id);
    } else {
      $invoice = new Invoice($invoice_id);
      $invoice->ClientData = new Client($invoice->client);
      $invoice->issue_date_datesafe = date("Y-m-d", $invoice->issue_date);
      $invoice->due_date_datesafe = date("Y-m-d", $invoice->due_date);
      $invoice->issue_date_humanreadable = date("d-m-Y", $invoice->issue_date);
      $invoice->due_date_humanreadable = date("d-m-Y", $invoice->due_date);
      $invoice->total_price = money_format("%.2n", floatval($invoice->price));
      $invoice->work_description_br = nl2br($invoice->work_description);
    }
    if (isset($_POST["type"]) || !empty($_POST["type"])) {
      $type = Functions::getPostVarSafely("type");
      $ajaxRenderer = new Renderer;
      $ajaxRenderer->setTemplateVariable("Invoice", $invoice);
      if ($type == "details") {
        $ajaxRenderer->render("invoicedetails.tmpl");
      } elseif ($type == "edit") {
        $ajaxRenderer->render("invoiceedit.tmpl");
      } else {
        echo "Type '" . $type .  "' not supported.";
      }
    } else {
      echo "Type parameter not provided.";
    }
  } else {
    echo "Invoice ID parameter not provided.";
  }
