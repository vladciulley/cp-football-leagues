CP Football Leagues
-------------------

### Notes

* The project was built on PHP 7.2.8. I have tested it on 7.1.3 and it is not compatible.
* My intention was to build the JWT authentication from scratch, but I found it to be way too 
complex for a test, so I used ```rbdwllr/reallysimplejwt``` library and implement it using 
Symfony's ```AbstractGuardAuthenticator```. You can find the implementation in ```App\Jwt```.
* The JWT authentication does not have automated token refresh, the TTL is set to 60 minutes. 
You can change this setting in services.yaml, under ```App\Jwt\TokenManager``` configuration key.
* For request validation I used Symfony Collection Validator to make sure that
no extra parameters are sent and that the ones that are sent are correct. You can find the 
implementation in ```App/Validator```.
* When the validator fails the errors are converted to an array of strings using a Symfony 
Transformer. The transformer is located in ```App/Transformer/ViolationDataTransformer```.
* The Symfony BadRequestHttpException was extended in ```App\Exception``` so I would be able to 
add context data to such exceptions, sending concrete validation error messages to API clients.
* Since nothing was specified in the test specs regarding whether the orphan teams should be 
deleted or not when deleting a league, I chose to also remove them.
* The tests I have created are functional tests for the API endpoints, no unit tests for services
created, but since the API uses the services, they are covered on some extent.
* I have not used a different database for testing, since this is just a test project,
hence I have built a set of test fixtures and a mechanism that creates them when the test begins 
and deletes them when completed.
* I have used Sensio's ParamConverter and symfony-bundles/json-request-bundle for thinner controllers.
* I have created an exception listener to deal with 400, 404, 405 and 500 errors, so they will also 
return application/json responses. It can be found in ```App\EventListener```.
* Furthermore, I would add:
  * Unit tests for services ```App\Service\LeagueManager``` and ```App\Service\TeamManager```. 
  * An entity for the JSON responses that are currently simple arrays.
  * A fallback in the exception listener to cover all possible errors, not just specific ones, 
  so all responses would be JSON formatted.

### Installation

* ```git clone https://github.com/vladciulley/cp-football-leagues```
* ```composer install```
* update ```.env``` with database credentials
* update ```phpunit.xml``` with database credentials
* ```php bin/console doctrine:migrations:migrate```
* ```php bin/console doctrine:fixtures:load```

### Test Data
* Authenticate ```POST /login```
```
{
	"username": "user1@localhost",
	"password": "pass1"
}
```
* Create team ```POST /teams```
```
{
	"name": "Newly Created Team Name",
	"strip": "turquoise/pink",
	"league_id": "1"
}
```
* Update team ```PUT /teams/{id}```
```
{
	"name": "Updated Team Name",
	"strip": "pink/turquoise",
	"league_id": "1"
}
```
* Get team ```GET /teams/{id}```
* Get league ```GET /leagues/{id}```
* Get league teams ```GET /leagues/{id}/teams```
* Delete league ```DELETE /leagues/{id}```