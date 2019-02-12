<?php
namespace GameX\Core\Validate\Rules;

class IPv4 extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return filter_var($value, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_IPV4]) !== false
            ? $value
            : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['ipv4'];
    }
}
