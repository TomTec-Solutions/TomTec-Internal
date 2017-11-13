<?php
  class Renderer extends TTS_Framework {
    protected $pathToTemplates;
    protected $templateVariables;
    
    /**
     * Renderer constructor.
     */
    public function __construct() {
      $this->pathToTemplates = (parent::BASE_PATH . "/assets/templates");
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setTemplateVariable($key, $value) {
      return $this->templateVariables[$key] = $value;
    }
    
    /**
     * @param $template
     * @param bool $returnRender
     * @param bool $returnError
     * @return bool|string
     * @throws Exception
     */
    public function render($template, $returnRender = false, $returnError = false) {
      try {
        $loader = new Twig_Loader_Filesystem($this->pathToTemplates);
        if (!parent::DEBUG) {
          $twig = new Twig_Environment($loader, array("debug" => false));
        } else {
          $twig = new Twig_Environment($loader, array("debug" => true));
          $twig->addExtension(new Twig_Extension_Debug());
        }
        $template = $twig->loadTemplate($template);
        if ($returnRender) {
          return $template->render($this->templateVariables);
        } else {
          echo $template->render($this->templateVariables);
          return true;
        }
      } catch (Throwable $error) {
        if ($returnError) {
          throw new Exception($error->getMessage());
        } else {
          if (parent::DEBUG) {
            $renderer = new Renderer;
            $renderer->setTemplateVariable("error", $error->getMessage());
            $renderer->render("error.tmpl");
            unset($renderer);
          }
        }
      }
    }
    
    /**
     * Sets alert
     *
     * @param $type
     * @param $error
     */
    public function setAlert($type, $error) {
      $this->templateVariables["alert"] = array(
        "type" => $type,
        "text" => $error,
      );
    }
    
    /**
     * Clears alert
     */
    public function clearAlert() {
      unset($this->templateVariables["alert"]);
    }
  }
