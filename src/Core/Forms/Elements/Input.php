<?php
namespace GameX\Core\Forms\Elements;

use \GameX\Core\Forms\Element;

abstract class Input implements Element {

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var null|string
     */
    protected $formName = null;

    /**
     * @var bool
     */
    protected $hasError = false;

    /**
     * @var string
     */
    protected $error;

    /**
     * Input constructor.
     * @param string $name
     * @param mixed $value
     * @param array $options
     */
    public function __construct($name, $value, array $options = []) {
        $this->name = (string) $name;
        $this->setValue($value);
        $this->id = $this->replaceIdValue(
        	array_key_exists('id', $options) ? (string) $options['id'] : $this->generateFieldId($name)
		);
        $this->title = array_key_exists('title', $options) ? (string) $options['title'] : ucfirst($name);
        if (array_key_exists('required', $options)) {
            $this->isRequired = (bool) $options['required'];
        }
        if (array_key_exists('classes', $options)) {
            $this->classes = (array) $options['classes'];
        }
        if (array_key_exists('attributes', $options)) {
            $this->attributes = (array) $options['attributes'];
        }
        if (array_key_exists('error', $options)) {
            $this->error = (string) $options['error'];
        }
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name) {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getIsRequired() {
        return $this->isRequired;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getClasses() {
        return $this->classes;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getFormName() {
        return $this->formName;
    }

    /**
     * @inheritdoc
     */
    public function setFormName($formName) {
        $this->formName = (string) $formName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHasError() {
        return $this->hasError;
    }

    /**
     * @inheritdoc
     */
    public function setHasError($hasError) {
        $this->hasError = (bool) $hasError;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function setError($error) {
        $this->error = (string) $error;
        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateFieldId($name) {
        return 'input-' . ucfirst($this->name) . '-' . ucfirst($name);
    }

	/**
	 * @param string $text
	 * @return string
	 */
	protected function replaceIdValue($text) {
		$text = strtolower(htmlentities($text));
		$text = str_replace(get_html_translation_table(), "-", $text);
		$text = str_replace(" ", "-", $text);
		$text = preg_replace("/[-]+/i", "-", $text);
		return $text;
	}
}
