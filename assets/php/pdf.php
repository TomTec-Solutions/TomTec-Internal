<?php
  class PDF extends TTS_Framework {
    private $pdfurl, $data, $output, $flatten;

    public function __construct($pdfurl, $data) {
      $basePath = parent::BASE_PATH;
      if (strtolower($pdfurl) == "overdue") {
        $pdfurl = $basePath . "/assets/templates/pdf/invoice_overdue_template.pdf";
      } elseif (strtolower($pdfurl) == "paid") {
        $pdfurl = $basePath . "/assets/templates/pdf/invoice_paid_template.pdf";
      } else {
        $pdfurl = $basePath . "/assets/templates/pdf/invoice_template.pdf";
      }
      $this->pdfurl = $pdfurl;
      $this->data = $data;
    }

    private function generate() {
      $fdf = $this->makeFdf($this->data);
      $this->output = $this->tmpfile();
      exec("pdftk {$this->pdfurl} fill_form {$fdf} output {$this->output}{$this->flatten}");
      unlink($fdf);
    }

    public function makeFdf($data) {
      $fdf = "%FDF-1.2
        1 0 obj<</FDF<< /Fields[";
      foreach ($data as $key => $value) {
        $fdf .= "<</T(" . $key . ")/V(" . $value . ")>>";
      }
      $fdf .= "] >> >>
        endobj
        trailer
        <</Root 1 0 R>>
        %%EOF";
      $fdf_file = $this->tmpfile();
      file_put_contents($fdf_file, $fdf);
      return $fdf_file;
    }

    public function flatten() {
      $this->flatten = " flatten";
      return $this;
    }

    public function save($path = null) {
      if (is_null($path)) {
        return $this;
      }
      if (!$this->output) {
        $this->generate();
      }
      $dest = pathinfo($path, PATHINFO_DIRNAME);
      if (!file_exists($dest)) {
        mkdir($dest, 0775, true);
      }
      copy($this->output, $path);
      unlink($this->output);
      $this->output = $path;
      return $this;
    }

    public function download($invoice_id) {
      if (!$this->output) {
        $this->generate();
      }

      $filepath = $this->output;

      if (file_exists($filepath)) {
        header("Content-Description: File Transfer");
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=TomTecSolutions-$invoice_id.pdf");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($filepath));
        readfile($filepath);
        exit;
      }
    }

    private function tmpfile() {
      return tempnam(sys_get_temp_dir(), gethostname());
    }
  }
