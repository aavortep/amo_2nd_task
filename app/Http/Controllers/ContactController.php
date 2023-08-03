<?php

namespace App\Http\Controllers;

use \Datetime;
use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\AccountModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TaskModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\TasksCollection;

define('TOKEN_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

class ContactController extends Controller
{
    private function save_token($accessToken) {
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];
    
            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    private function get_token() {
        if (!file_exists(TOKEN_FILE)) {
            //exit('Access token file not found');
            $this->token_by_code();
        }
    
        $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);
    
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    public function token_by_code(Request $request) {
        session_start();
        
        $clientId = $_ENV['CLIENT_ID'];
        $clientSecret = $_ENV['CLIENT_SECRET'];
        $redirectUri = $_ENV['CLIENT_REDIRECT_URI'];

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $_SESSION['apiClient'] = $apiClient;

        if (isset($_GET['referer'])) {
            $apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
        if (isset($_GET['button'])) {
            echo $apiClient->getOAuthClient()->getOAuthButton(
                [
                    'title' => 'Установить интеграцию',
                    'compact' => true,
                    'class_name' => 'className',
                    'color' => 'default',
                    'error_callback' => 'handleOauthError',
                    'state' => $state,
                ]
            );
            die;
        } else {
            $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
                'state' => $state,
                'mode' => 'post_message',
            ]);
            header('Location: ' . $authorizationUrl);
            die;
        }
        } elseif (!isset($_GET['from_widget']) && (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state']))) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);

            if (!$accessToken->hasExpired()) {
                $this->save_token([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            die((string)$e);
        }
        $this->add($request);
    }

    private function add(Request $request) {
        $name = $request->input('name');
        $surname = $request->input('surname');
        $age = $request->input('age');
        $sex = $request->input('sex');
        $phone = $request->input('phone');
        $email = $request->input('email');

        $apiClient = $_SESSION['apiClient'];

        $accessToken = $this->get_token();

        $apiClient->setAccessToken($accessToken)
            ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    saveToken(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $baseDomain,
                        ]
                    );
                }
            );

        $contact = new ContactModel();
        $account = $apiClient->account()->getCurrent(AccountModel::getAvailableWith());
        $contact->setFirstName($name)
                ->setLastName($surname)
                ->setAccountId($account->getId());
        $customFields = $contact->getCustomFieldsValues();

        $phoneField = $customFields->getBy('code', 'PHONE');
        if (empty($phoneField)) {
            $phoneField = (new TextCustomFieldValuesModel())->setCode('PHONE');
            $customFields->add($phoneField);
        }
        $phoneField->setValues(
            (new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())->setValue($phone))
        );

        $emailField = $customFields->getBy('code', 'EMAIL');
        if (empty($emailField)) {
            $emailField = (new TextCustomFieldValuesModel())->setCode('EMAIL');
            $customFields->add($emailField);
        }
        $emailField->setValues(
            (new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())->setValue($email))
        );

        $sexField = $customFields->getBy('code', 'SEX');
        if (empty($sexField)) {
            $sexField = (new TextCustomFieldValuesModel())->setCode('SEX');
            $customFields->add($sexField);
        }
        $sexField->setValues(
            (new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())->setValue($sex))
        );
        
        $ageField = $customFields->getBy('code', 'AGE');
        if (empty($ageField)) {
            $ageField = (new NumericCustomFieldValuesModel())->setCode('AGE');
            $customFields->add($ageField);
        }
        $ageField->setValues(
            (new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())->setValue($age))
        );

        $contactModel = $apiClient->contacts()->addOne($contact);

        $this->link_lead($contact, $apiClient);
        //return $accessToken->getToken();
    }

    private function link_lead($contact, $apiClient) {
        $lead = new LeadModel();
        $now = new DateTime();
        $lead->setName("Сделка {$contact->getFirstName()} {$contact->getLastName()}")
            ->setPrice(54321)
            ->setAccountId($contact->getAccountId())->setCreatedAt($now->getTimestamp());
        $links = new LinksCollection();
        $links->add($contact);
        $apiClient->leads()->link($lead, $links);

        $this->link_task($lead, $apiClient);
    }

    private function link_task($lead, $apiClient) {
        $tasksCollection = new TasksCollection();
        $task = new TaskModel();
        $completeTill = $lead->getCreatedAt() + 4 * 24 * 60 * 60;
        $datetime = date("w G", $completeTill);
        $weektime = explode(" ", $datetime);
        if (int($weektime[1]) < 9) {
            $completeTill += (9 - int($weektime[1])) * 60 * 60;
        }
        elseif (int($weektime[1]) > 18) {
            $completeTill += (24 - $weektime[1] + 18) * 60 * 60;
        }
        $datetime = date("w G", $completeTill);
        $weektime = explode(" ", $datetime);
        if (int($weektime[0]) === 6) {
            $completeTill += 48 * 60 * 60;
        }
        elseif (int($weektime[0]) === 0) {
            $completeTill += 24 * 60 * 60;
        }
        $task->setTaskTypeId(TaskModel::TASK_TYPE_ID_CALL)
            ->setText('Новая задача')
            ->setCompleteTill($completeTill)
            ->setEntityType(EntityTypesInterface::LEADS)
            ->setEntityId($lead->getId())
            ->setResponsibleUserId(123);  // responsibleUserId!!!
        
        $tasksCollection->add($task);

        $tasksService = $apiClient->tasks();
        $tasksCollection = $tasksService->add($tasksCollection);
    }
}
