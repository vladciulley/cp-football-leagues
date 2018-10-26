<?php

namespace App\Controller;

use App\Entity\Team;
use App\Service\TeamManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teams")
 */
class TeamController extends RESTController
{

    /**
     * @Route("", name="api_teams_post", methods={"POST"})
     *
     * @param Request     $request
     * @param TeamManager $teamManager
     *
     * @return JsonResponse
     */
    public function createTeams(Request $request, TeamManager $teamManager): JsonResponse
    {
        $team = $teamManager->create(
            $request->get('name'),
            $request->get('strip'),
            $request->get('league_id')
        );

        if ($team) {
            return $this->json(null, JsonResponse::HTTP_CREATED, [
                'Location' => $this->generateUrl('api_teams_get', ['id' => $team->getId()]),
            ]);
        } else {
            return $this->json(null, JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}", name="api_teams_put", methods={"PUT"})
     *
     * @param Request     $request
     * @param Team        $team
     * @param TeamManager $teamManager
     *
     * @return JsonResponse
     * @ParamConverter("team", class="App:Team")
     *
     */
    public function updateTeams(Request $request, Team $team, TeamManager $teamManager): JsonResponse
    {
        $team = $teamManager->update(
            $team,
            $request->get('name'),
            $request->get('strip'),
            $request->get('league_id')
        );

        if ($team) {
            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } else {
            return $this->json(null, JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}", name="api_teams_get", methods={"GET"})
     *
     * @param Team $team
     *
     * @return JsonResponse
     * @ParamConverter("team", class="App:Team")
     */
    public function getTeams(Team $team): JsonResponse
    {
        return $this->json($team, JsonResponse::HTTP_OK);
    }
}
