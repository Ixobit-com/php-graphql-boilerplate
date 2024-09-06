<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DataFixtures\UserFixtures;
use PHPUnit\Framework\Constraint\RegularExpression;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseServiceWebTestCase extends WebTestCase
{
    protected ?KernelBrowser $client;
    protected ?TranslatorInterface $translator;

    public function setUp(): void
    {
        $this->client         = static::createClient();
        $this->translator     = static::getContainer()->get(TranslatorInterface::class);
        parent::setUp();
    }

    public function tearDown(): void
    {
        restore_exception_handler(); // some vendor(s) forgot to reset exception handler...
        parent::tearDown();
    }

    /**
     * Login user by username and password.
     */
    protected function loginAs(string $username, string $password = UserFixtures::DEFAULT_PASSWORD): \stdClass
    {
        $this->call('auth',
            [
                'query'     => 'query login($loginInfo: loginInputDTO!) { login(loginInfo: $loginInfo) { user { login } token refresh_token }}',
                'variables' => '{ "loginInfo": { "login": "'.$username.'", "password": "'.$password.'" } }',
            ]
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        if (isset($jsonResponse->data->login->token)) {
            $this->client->setServerParameter('HTTP_Authorization', 'Bearer '.$jsonResponse->data->login->token);
            return $jsonResponse;
        }
        return $jsonResponse;
    }


    protected function analyzeResponse(\stdClass $response, array $responseAnalyzers, array $context = []): \stdClass
    {
        foreach ($responseAnalyzers as $analyzer) {
            if (is_callable($analyzer)) {
                $this->assertTrue(
                    $analyzer($response, $context),
                    'Analyzer error'
                );
            }
        }
        return $response;
    }


    /**
     * @param array $expectedErrorMessages
     *                                     Error messages expected in response, may be represented by:
     *                                     - string for translator (include domain): <translator domain>:<message_id>
     *                                     - RegularExpression object
     */
    protected function analyzeResponseErrors(\stdClass $response, array $expectedErrorMessages): \stdClass
    {
        $responseMessages = $this->getMessagesFromResponse($response);

        if (!empty($expectedErrorMessages)) { // error expected
            if (count($responseMessages) > 0) { // errors or warnings exists
                foreach ($expectedErrorMessages as $expectedMessage) {
                    $this->assertTrue(
                        $this->responseChecker($expectedMessage, $responseMessages, $response),
                        "Error '".$this->messageCheckerToString($expectedMessage)."' is not found. Messages: ".
                        implode("\n", $this->getMessagesFromResponse($response))
                    );
                }
            } else {
                $this->fail(
                    'No errors in response, but expected errors: '.
                    implode(';',
                        array_map(function ($messageChecker) {
                            return $this->messageCheckerToString($messageChecker);
                        }, $expectedErrorMessages)
                    )
                );
            }
        } else {
            $this->assertEmpty(
                $responseMessages,
                'Response has errors, but test not expect them. Messages: '.
                implode("\n", $responseMessages));
        }

        return $response;
    }

    protected function call(string $schema, array $parameters): Crawler
    {
        return $this->client->request(
            method: 'POST',
            uri: static::getContainer()->getParameter('app.api_url').'/graphql/'.$schema,
            parameters: $parameters
        );
    }

    private function responseChecker(
        string|RegularExpression|callable $error,
        array $responseErrorMessages,
        \stdClass $response
    ): bool {
        // error message in format <translator domain>:message_id
        // Example: messages:user.not.found
        if (is_string($error)) { // convert into RegularExpression
            $domain = 'messages';
            if (strpos($error, ':') > 0) {
                [$domain, $message] = explode(':', $error);
            } else {
                $message = $error;
            }

            $errorMessageRegexp = preg_quote($this->translator->trans(id: $message, domain: $domain));
            $errorMessageRegexp = '/'.preg_replace('/\\\{\\\{.*\\\}\\\}/iUs', '.*', $errorMessageRegexp).'/iUs';
            $error              = new RegularExpression($errorMessageRegexp);
        }

        return count(array_filter(
            $responseErrorMessages,
            function ($message) use ($error) {
                try {
                    return is_null($error->evaluate($message));
                } catch (\Throwable $e) {
                    return false;
                }
            }
        )) > 0;
    }

    private function getMessagesFromResponse(\stdClass $response): array
    {
        $messages = [];
        if (!empty($response->errors)) {
            foreach ($response->errors as $error) {
                $messages[] = $error->message.' [Debug: '.($error->extensions->debugMessage ?? '').']';
                if (isset($error->state)) {
                    $vars = get_object_vars($error->state);
                    foreach ($vars as $var_key => $errors) {
                        foreach ($errors as $err) {
                            $messages[] = $err->message;
                        }
                    }
                }
            }
        }
        if (!empty($response->extensions->warnings)) {
            foreach ($response->extensions->warnings as $warning) {
                $messages[] = $warning->message.' [Debug: '.($error->extensions->debugMessage ?? '').']';
                if (isset($warning->state)) {
                    $vars = get_object_vars($warning->state);
                    foreach ($vars as $var_key => $warnings) {
                        foreach ($warnings as $warn) {
                            $messages[] = $warn->message;
                        }
                    }
                }
            }
        }

        return $messages;
    }

    private function messageCheckerToString(string|RegularExpression $error): string
    {
        return match (true) {
            is_string($error)                   => $error,
            $error instanceof RegularExpression => $error->toString(),
        };
    }
}
