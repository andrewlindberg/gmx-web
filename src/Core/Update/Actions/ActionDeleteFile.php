<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;

class ActionDeleteFile implements ActionInterface {

    /**
     * @var string
     */
    protected $destination;

    /**
     * @param string $destination
     */
    public function __construct($destination) {
        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function run() {
        if (!is_file($this->destination)) {
            return false;
        }
        return unlink($this->destination);
    }
}