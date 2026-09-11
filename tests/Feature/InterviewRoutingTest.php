<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regression cover for the route group that took six unrelated pages down.
 *
 * The interview routes were declared inside a
 * `prefix('interviews/{interview}/attempts')` group that also contained
 * `apiResource('interviews', ...)`, producing a URI with `{interview}` in it
 * twice. Symfony compiles routes lazily while matching, so that one malformed
 * pattern threw a LogicException out of the entire matching pass — every
 * request that reached it 500'd, including requests for pages that had nothing
 * to do with interviews.
 *
 * These tests fail loudly if that shape is ever reintroduced.
 */
class InterviewRoutingTest extends TestCase
{
    /**
     * Resolve a URI the way the router does, returning the matched action.
     */
    private function matchAction(string $method, string $uri): string
    {
        return Route::getRoutes()
            ->match(Request::create($uri, $method))
            ->getActionName();
    }

    public function test_no_route_declares_the_same_parameter_twice(): void
    {
        foreach (Route::getRoutes() as $route) {
            preg_match_all('/\{(\w+)\??\}/', $route->uri(), $matches);
            $names = $matches[1];

            $this->assertSame(
                count($names),
                count(array_unique($names)),
                "Route [{$route->uri()}] declares a parameter more than once. "
                .'Symfony refuses to compile this, and because compilation '
                .'happens during matching it breaks every route, not just this one.'
            );
        }
    }

    /**
     * The six pages the malformed route took down as collateral damage.
     *
     * @dataProvider unrelatedPages
     */
    public function test_pages_unrelated_to_interviews_still_resolve(string $uri): void
    {
        $action = $this->matchAction('GET', $uri);

        $this->assertNotSame('', $action, "[{$uri}] did not resolve to an action.");
    }

    public static function unrelatedPages(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'job listing' => ['/job-listing'],
            'job applicants' => ['/job-applicants'],
            'company profile' => ['/company-profile'],
            'messages' => ['/messages'],
            'language switcher' => ['/language/jp'],
            'project index' => ['/project'],
            'talents' => ['/talents'],
        ];
    }

    /**
     * Each interview endpoint must reach its own controller action.
     *
     * The second defect in the original group was quieter than the crash:
     * `GET /`, `POST /` and `PUT /` were each registered three times against
     * the same URI, so last-registration-wins silently left the attempt and
     * question collections unreachable behind the answer handlers.
     *
     * @dataProvider interviewEndpoints
     */
    public function test_interview_endpoint_reaches_its_own_controller(
        string $method,
        string $uri,
        string $expected
    ): void {
        $this->assertStringContainsString($expected, $this->matchAction($method, $uri));
    }

    public static function interviewEndpoints(): array
    {
        return [
            ['GET', '/interviews', 'InterviewController@index'],
            ['POST', '/interviews', 'InterviewController@store'],
            ['GET', '/interviews/5', 'InterviewController@show'],

            ['GET', '/interviews/5/attempts', 'InterviewAttemptController@index'],
            ['POST', '/interviews/5/attempts', 'InterviewAttemptController@store'],
            ['GET', '/interviews/5/attempts/7', 'InterviewAttemptController@show'],

            ['POST', '/interviews/5/attempts/7/start', 'InterviewAttemptController@start'],
            ['POST', '/interviews/5/attempts/7/begin', 'InterviewAttemptController@begin'],
            ['POST', '/interviews/5/attempts/7/complete', 'InterviewAttemptController@complete'],
            ['POST', '/interviews/5/attempts/7/fail', 'InterviewAttemptController@fail'],
            ['POST', '/interviews/5/attempts/7/no-answer', 'InterviewAttemptController@noAnswer'],
            ['POST', '/interviews/5/attempts/7/cancel', 'InterviewAttemptController@cancel'],

            ['GET', '/interviews/5/attempts/7/questions', 'InterviewQuestionController@index'],
            ['POST', '/interviews/5/attempts/7/questions', 'InterviewQuestionController@store'],
            ['GET', '/interviews/5/attempts/7/questions/9', 'InterviewQuestionController@show'],
            ['DELETE', '/interviews/5/attempts/7/questions/9', 'InterviewQuestionController@destroy'],

            ['GET', '/interviews/5/attempts/7/questions/9/answer', 'InterviewAnswerController@show'],
            ['POST', '/interviews/5/attempts/7/questions/9/answer', 'InterviewAnswerController@store'],
            ['PUT', '/interviews/5/attempts/7/questions/9/answer', 'InterviewAnswerController@update'],

            ['GET', '/interviews/5/attempts/7/evaluation', 'InterviewEvaluationController@show'],
            ['POST', '/interviews/5/attempts/7/evaluation', 'InterviewEvaluationController@store'],
        ];
    }

    public function test_every_registered_route_can_be_compiled(): void
    {
        foreach (Route::getRoutes() as $route) {
            try {
                $route->toSymfonyRoute()->compile();
            } catch (\Throwable $e) {
                $this->fail(
                    "Route [{$route->uri()}] cannot be compiled: {$e->getMessage()}"
                );
            }
        }

        $this->assertTrue(true);
    }
}
