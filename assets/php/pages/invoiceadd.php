<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["selected_client"]) || empty($_POST["selected_client"])) {
      $rend->setAlert("warning", "No client was selected.");
    } else {
      $data = array(
        "client" => Functions::getPostVarSafely("selected_client"),
        "issue_date" => strtotime(Functions::getPostVarSafely("issue_date")),
        "due_date" => strtotime(Functions::getPostVarSafely("due_date")),
        "technician" => Functions::getPostVarSafely("technician"),
        "total_price" => money_format("%.2n", floatval(Functions::getPostVarSafely("price"))),
        "work_description" => Functions::getPostVarSafely("work_description"),
        "state" => "Awaiting Payment",
      );
      $invoice_id = intval(Functions::getPostVarSafely("invoice_id"));
      if (!Invoice::exists($invoice_id)) {
        Invoice::add($data, $invoice_id);
        $rend->setAlert("success", "Added new invoice #" . $invoice_id . " successfully.");
      } else {
        $rend->setAlert("danger", "Invoice #" . $invoice_id . " already exists in the database.");
      }
    }
  }

  $date = strtotime("+7 day", strtotime(date("Y-m-d")));
  $rend->setTemplateVariable("SevenDaysFromNow", date("Y-m-d", $date));
  $rend->setTemplateVariable("ClientData", Client::allClients());
  $rend->setTemplateVariable("new_invoice_id", Invoice::getNewInvoiceId());
  unset($date);
