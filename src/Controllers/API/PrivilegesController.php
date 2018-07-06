<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Group;
use \GameX\Models\Privilege;

class PrivilegesController extends BaseApiController {

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return static
	 */
    public function indexAction(Request $request, Response $response, array $args) {
        $privileges = [];
        /** @var Group $group */
        $groups = Group::whereHas('players', function ($query) {
            $query
                ->where('active', '=', '1')
                ->where('expired_at', '>=', Carbon::today()->toDateString());
        })
            ->where('server_id', '=', $request->getAttribute('server_id'))
            ->get();


        foreach ($groups as $group) {
        	/** @var Privilege $privilege */
			foreach ($group->players as $privilege) {
				$player = $privilege->player;
                $privileges[] = [
                    'player_id' => $player->id,
                    'group_id' => $group->id,
                    'steamid' => $player->steamid,
                    'nick' => $player->nick,
                    'auth_type' => $player->auth_type,
                    'password' => $player->password ?: '',
                    'prefix' => $privilege->prefix ?: $group->title,
					'flags' => $group->flags,
					'expired' => $privilege->expired()->getTimestamp(),
                    'priority' => $group->priority,
                    'access' => $player->access,
                ];
            }
        }

        return $response->withJson([
			'success' => true,
			'privileges' => $privileges,
        ]);
    }
}
