<?php
namespace GameX\Core\Forms;

use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Session\Session;
use \GameX\Core\Lang\Language;
use \GameX\Core\Forms\Elements\File;
use \ArrayAccess;
use \Exception;

class Form implements ArrayAccess {
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var Language
     */
    protected $language;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isSubmitted = false;

    /**
     * @var bool
     */
    protected $isValid = true;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var string
     */
    protected $action;

    /**
     * @var Element[]
     */
    protected $elements = [];
    
    /**
     * @var Rule[][]
     */
    protected $rules = [];

    /**
     * Form constructor.
     * @param Session $session
     * @param Language $language
     * @param $name
     */
    public function __construct(Session $session, Language $language, $name) {
        $this->session = $session;
        $this->language = $language;
        $this->name = $name;

        $this->loadValues();
    }

    /**
     * From destructor
     */
    public function __destruct() {
        if (!$this->isValid) {
            $this->writeValues();
        }
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action) {
		$this->action = (string) $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param Element $element
     * @return $this
     */
    public function add(Element $element) {
        $this->elements[$element->getName()] = $element;

        if (array_key_exists($element->getName(), $this->values)) {
            $element->setValue($this->values[$element->getName()]);
        }

        if (array_key_exists($element->getName(), $this->errors)) {
            $element->setHasError(true)->setError($this->errors[$element->getName()]);
        }

        $element->setFormName($this->name);

        return $this;
    }

    /**
     * @param string $name
     * @return Element
     * @throws Exception
     */
    public function get($name) {
        if (!$this->exists($name)) {
            throw new Exception('Element not found');
        }

        return $this->elements[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name) {
        return array_key_exists($name, $this->elements);
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return $this
     */
    public function processRequest(ServerRequestInterface $request) {
        if (!$request->isPost()) {
            return $this;
        }

        $body = $request->getParsedBody();
        $values = array_key_exists($this->name, $body) && is_array($body[$this->name])
            ? $body[$this->name]
            : [];

        $body = $request->getUploadedFiles();
        $files = array_key_exists($this->name, $body) && is_array($body[$this->name])
            ? $body[$this->name]
            : [];

        $this->isSubmitted = true;
        $this->isValid = true;
        foreach ($this->elements as $element) {
            if ($element instanceof File) {
                if (array_key_exists($element->getName(), $files)) {
                    /** @var \Slim\Http\UploadedFile $file */
                    $file = $files[$element->getName()];
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $element->setValue(null);
                    } else {
                        $element->setValue($file);
                    }
                } else {
                    $element->setValue(null);
                }
            } else {
                if (array_key_exists($element->getName(), $values)) {
                    $element->setValue($values[$element->getName()]);
                } else {
                    $element->setValue('');
                }
            }
            
            $this->processValidate($element);
            
        }
        return $this;
    }

    public function saveValues() {
        $this->writeValues();
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSubmitted() {
        return $this->isSubmitted;
    }

    /**
     * @return bool
     */
    public function getIsValid() {
        return $this->isValid;
    }

    /**
     * @return string[]
     */
    public function getValues() {
    	$values = [];
    	foreach ($this->elements as $element) {
    		$values[$element->getName()] = $element->getValue();
		}
        return $values;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getValue($name) {
        return $this->get($name)->getValue();
    }

    /**
     * @param $name
     * @param $error
     * @return $this
     */
    public function setError($name, $error) {
        $this
            ->get($name)
            ->setHasError(true)
            ->setError($error);
        $this->isValid = false;
        return $this;
    }
    
    public function addRule($key, Rule $rule) {
        if (!array_key_exists($key, $this->rules)) {
            $this->rules[$key] = [];
        }
        
        $this->rules[$key][] = $rule;
        return $this;
    }

    public function offsetExists($name) {
        return $this->exists($name);
    }

    public function offsetGet($name) {
        return $this->get($name);
    }

    public function offsetSet($name, $value) {}
    public function offsetUnset($name) {}

    /**
     * Load values and errors from session
     */
    protected function loadValues() {
        $key = $this->getSessionKey();
        $sessionData = $this->session->get($key);
        if ($sessionData !== null) {
            $this->values = $sessionData['values'];
            $this->errors = $sessionData['errors'];
            $this->session->delete($key);
        }
    }

    /**
     * Save values and errors to session
     */
    protected function writeValues() {
        $values = []; $errors = [];
        foreach ($this->elements as $element) {
            if ($element->getType() != 'password') {
                $values[$element->getName()] = $element->getValue();
            }
            if ($element->getHasError()) {
                $errors[$element->getName()] = $element->getError();
            }
        }
		$key = $this->getSessionKey();
        $this->session->set($key, [
        	'values' => $values,
        	'errors' => $errors,
		]);
    }

    protected function getSessionKey() {
        return 'form_' . $this->name;
    }

    protected function processValidate(Element $element) {
    	if (!array_key_exists($element->getName(), $this->rules)) {
    		return;
		}
		$name = $element->getName();
		foreach ($this->rules[$name] as $rule) {
			if(!$rule->validate($this, $name)) {
				$this->isValid = false;
				$element
					->setHasError(true)
					->setError($rule->getMessage($this->language));
				break;
			}
		}
	}
}
