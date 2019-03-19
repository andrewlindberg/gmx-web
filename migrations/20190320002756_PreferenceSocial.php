<?php

use \GameX\Core\Migration;
use \GameX\Models\Preference;

class PreferenceSocial extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        Preference::create([
            'key' => 'social_auth',
            'value' => [
                'steam' => [
                    'enabled' => false,
                    'icon' => 'fab fa-steam',
                ],
                'vk' => [
                    'enabled' => false,
                    'icon' => 'fab fa-vk',
                    'id' => null,
                    'key' => null,
                    'secret' => null
                ],
                'facebook' => [
                    'enabled' => false,
                    'icon' => 'fab fa-facebook',
                    'id' => null,
                    'key' => null,
                    'secret' => null
                ],
                'discord' => [
                    'enabled' => false,
                    'icon' => 'fab fa-discord',
                    'id' => null,
                    'key' => null,
                    'secret' => null
                ]
            ]
        ]);
    }
    
    /**
     * Undo the migration
     */
    public function down() {
        Preference::where('key', 'social_auth')->delete();
    }
}

