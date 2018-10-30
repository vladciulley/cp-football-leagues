<?php

namespace App\Controller;

use App\Entity\Team;
use App\Service\TeamManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teams")
 */
class TeamController extends RESTController
{

    /**
     * Creates a new team.
     * POST /teams
     *
     * Request example:
     * <code>
     * {
     *     "name": "New Team Name",
     *     "strip": "red/blue"
     *     "league_id": 1
     * }
     * </code>
     *
     * @Route("", name="api_teams_post", methods={"POST"})
     *
     * @param Request     $request
     * @param TeamManager $teamManager
     *
     * @return JsonResponse
     */
    public function createTeams(Request $request, TeamManager $teamManager): JsonResponse
    {
        $team = $teamManager->create($request->request->all());

        if ($team) {
            return $this->json(null, JsonResponse::HTTP_CREATED, [
                'Location' => $this->generateUrl('api_teams_get', ['id' => $team->getId()]),
            ]);
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * Updates an existing team.
     * PUT /teams
     * 
     * Request example:
     * <code>
     * {
     *     "name": "Updated Team Name",
     *     "strip": "red/blue"
     *     "league_id": 1
     * }
     * </code>
     * 
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
        $team = $teamManager->update($team, $request->request->all());

        if ($team) {
            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } else {
            throw new BadRequestHttpException();
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
