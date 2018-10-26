<?php

namespace App\Controller;

use App\Entity\League;
use App\Service\LeagueManager;
use App\Service\TeamManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/leagues")
 */
class LeagueController extends RESTController
{

    /**
     * @Route("/{id}", name="api_leagues_get", methods={"GET"})
     *
     * @param League $league
     *
     * @return JsonResponse
     * @ParamConverter("league", class="App:League")
     *
     */
    public function getLeagues(League $league): JsonResponse
    {
        return $this->json($league, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="api_leagues_delete", methods={"DELETE"})
     *
     * @param League        $league
     * @param LeagueManager $leagueManager
     *
     * @return JsonResponse
     * @ParamConverter("league", class="App:League")
     *
     */
    public function deleteLeagues(League $league, LeagueManager $leagueManager): JsonResponse
    {
        $leagueManager->delete($league);

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/teams", name="api_leagues_get_teams", methods={"GET"})
     *
     * @param League      $league
     * @param TeamManager $teamManager
     *
     * @return JsonResponse
     * @ParamConverter("league", class="App:League")
     *
     */
    public function getLeaguesTeams(League $league, TeamManager $teamManager): JsonResponse
    {
        $teams = $teamManager->getByLeague($league);

        return $this->json($teams, JsonResponse::HTTP_OK);
    }
}