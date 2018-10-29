CP Football Leagues
-------------------

### Notes

* The project was built on PHP 7.2.8. I have tested it on 7.1.3 and it is not compatible.
* My intention was to build the JWT authentication from scratch, but I found it to be way too 
complex for a test, so I used ```rbdwllr/reallysimplejwt``` library and implement it using 
Symfony's ```AbstractGuardAuthenticator```.
* The JWT authentication does not have automated token refresh, the TTL is set to 60 minutes. 
You can change this setting in services.yaml, under ```App\Jwt\TokenManager```.
* Since nothing was specified in the test specs regarding whether the orphan teams should be 
deleted or not when deleting a league, I chose to also remove them.
* The team update endpoint spec stated that it should _"Replace all attributes of a football team"_ 
so I allowed empty team name and strip, but returned a HTTP Bad Request when the league is missing.
* The tests I have created are for the API endpoints (no services tests), but since the API 
uses the services, they are covered on some extent.
* I have not used a different database for testing, since this is just a test project,
hence I have built a set of test fixtures and a mechanism that creates them when the test begins 
and deletes them when completed.
* I have used Sensio's ParamConverter and symfony-bundles/json-request-bundle for thinner controllers.
* I have created an ExceptionListener to deal with 400, 404, 405 and 500 errors, so they will also 
return application/json responses.
* Things that I would have done further would be creating a validation service using Symfony validators to 
check that no extra fields are present on the request data and that the fields received have proper 
values. When validation fails it should return a HTTP Bad Request with the violation messages. The
violation messages could be transformed from the returned ```ConstraintViolationList``` to a messages
array using a custom data transformer.

### Installation

* git clone https://github.com/vladciulley/cp-football-leagues
* composer install
* update ```.env``` with database credentials
* update ```phpunit.xml``` with database credentials
* php bin/console doctrine:migrations:migrate
* php bin/console doctrine:fixtures:load

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
