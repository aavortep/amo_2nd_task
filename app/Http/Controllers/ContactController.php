<?php

namespace App\Http\Controllers;

use \Datetime;
use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\AccountModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TaskModel;
use AmoCRM\Models\Customers\CustomerModel;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Filters\EntitiesLinksFilter;
use AmoCRM\Models\CustomFields\TextCustomFieldModel;
use AmoCRM\Models\CustomFields\NumericCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\NotesCollection;

define('TOKEN_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');
define('DATA_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'contact_data.json');

class ContactController extends Controller
{
    private function save_token($accessToken)
    {
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

    private function save_data($contactData)
    {
        if (
            isset($contactData)
            && isset($contactData['name'])
            && isset($contactData['surname'])
            && isset($contactData['age'])
            && isset($contactData['sex'])
            && isset($contactData['phone'])
            && isset($contactData['email'])
            && ($contactData['age'] > 0)
        ) {
            file_put_contents(DATA_FILE, json_encode($contactData));
        } else {
            exit('Введенные данные некорректны');
        }
        return json_encode($contactData);
    }

    private function get_token()
    {
        if (!file_exists(TOKEN_FILE)) {
            exit('Access token file not found');
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

    private function get_data()
    {
        if (!file_exists(DATA_FILE)) {
            exit('Contact data file not found');
        }
        $contactData = json_decode(file_get_contents(DATA_FILE), true);
        return $contactData;
    }

    private function connect_to_client(Request $request)
    {
        session_start();

        $clientId = $_ENV['CLIENT_ID'];
        $clientSecret = $_ENV['CLIENT_SECRET'];
        $redirectUri = $_ENV['CLIENT_REDIRECT_URI'];

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

        if (!file_exists(TOKEN_FILE)) {
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
                        'api_client' => $apiClient,
                    ]);
                    header('Location: ' . $authorizationUrl);
                    die;
                }
            }

            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);

            if (!$accessToken->hasExpired()) {
                $this->save_token([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]);
            }
        } else {
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
        }

        return $apiClient;
    }

    public function token_by_code(Request $request)
    {
        $apiClient = $this->connect_to_client($request);
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

        $contactData = $this->get_data();
        $this->add_contact($contactData, $apiClient);
        return redirect('/');
    }

    public function auth(Request $request)
    {
        $this->save_data($request->input());

        $apiClient = $this->connect_to_client($request);

        $contactData = $this->get_data();
        $this->add_contact($contactData, $apiClient);
        return redirect('/');
    }

    private function is_duplicate($contactData, $apiClient)
    {
        $contacts = $apiClient->contacts()->get();
        foreach ($contacts as $contact) {
            $customFieldsValues = $contact->getCustomFieldsValues();
            if ($customFieldsValues) {
                $phoneField = $customFieldsValues->getBy('fieldCode', 'PHONE');
                if ($phoneField && $phoneField->getValues()->first()->getValue() == $contactData['phone']) {
                    $duplicate = $contact;
                    break;
                }
            }
        }
        if (empty($duplicate)) {
            return null;
        } else {
            return $duplicate;
        }
    }

    private function add_contact($contactData, $apiClient)
    {
        $duplicate = $this->is_duplicate($contactData, $apiClient);
        if ($duplicate) {
            $linksService = $apiClient->links(EntityTypesInterface::CONTACTS);
            $filter = new EntitiesLinksFilter([$duplicate->getId()]);
            $contactsLeads = $linksService->get($filter)->getBy('toEntityType', 'leads');

            $linkedLead = $apiClient->leads()->getOne($contactsLeads->getToEntityId());
            if ($linkedLead->getStatusId() == 142) {
                $customersService = $apiClient->customers();
                $customer = new CustomerModel();
                $customer->setName("Покупатель {$duplicate->getFirstName()} {$duplicate->getLastName()}");
                $customerModel = $customersService->addOne($customer);

                $links = new LinksCollection();
                $links->add($duplicate);
                $customersService->link($customerModel, $links);
            } else {
                $notesCollection = new NotesCollection();
                $commonNote = new CommonNote();
                $commonNote->setEntityId($linkedLead->getId())
                    ->setText('Для создания покупателя нужно перевести сделку в статус "Успешно реализовано"');
                $notesCollection->add($commonNote);

                $leadNotesService = $apiClient->notes(EntityTypesInterface::LEADS);
                $leadNotesService->add($notesCollection);
            }
        } else {
            $contact = new ContactModel();
            $account = $apiClient->account()->getCurrent(AccountModel::getAvailableWith());
            $contact->setFirstName($contactData['name'])
                ->setLastName($contactData['surname'])
                ->setAccountId($account->getId());

            $this->add_fields($apiClient);
            $customFields = new CustomFieldsValuesCollection();

            $phoneField = $customFields->getBy('code', 'PHONE');
            if (empty($phoneField)) {
                $phoneField = (new TextCustomFieldValuesModel())->setFieldCode('PHONE');
            }
            $phoneField->setValues(
                (new TextCustomFieldValueCollection())
                    ->add((new TextCustomFieldValueModel())->setValue($contactData['phone']))
            );
            $customFields->add($phoneField);

            $emailField = $customFields->getBy('code', 'EMAIL');
            if (empty($emailField)) {
                $emailField = (new TextCustomFieldValuesModel())->setFieldCode('EMAIL');
            }
            $emailField->setValues(
                (new TextCustomFieldValueCollection())
                    ->add((new TextCustomFieldValueModel())->setValue($contactData['email']))
            );
            $customFields->add($emailField);

            $sexField = $customFields->getBy('code', 'SEX');
            if (empty($sexField)) {
                $sexField = (new TextCustomFieldValuesModel())->setFieldCode('SEX');
            }
            $sexField->setValues(
                (new TextCustomFieldValueCollection())
                    ->add((new TextCustomFieldValueModel())->setValue($contactData['sex']))
            );
            $customFields->add($sexField);

            $ageField = $customFields->getBy('code', 'AGE');
            if (empty($ageField)) {
                $ageField = (new NumericCustomFieldValuesModel())->setFieldCode('AGE');
            }
            $ageField->setValues(
                (new NumericCustomFieldValueCollection())
                    ->add((new NumericCustomFieldValueModel())->setValue($contactData['age']))
            );
            $customFields->add($ageField);

            $contact->setCustomFieldsValues($customFields);

            $contactModel = $apiClient->contacts()->addOne($contact);

            $this->link_lead($contactModel, $apiClient);
        }
    }

    private function add_fields($apiClient)
    {
        $customFieldsService = $apiClient->customFields(EntityTypesInterface::CONTACTS);
        $customFieldsCollection = new CustomFieldsCollection();

        $result = $customFieldsService->get();

        if (!$result->getBy('code', 'SEX')) {
            $sex = new TextCustomFieldModel();
            $sex->setName('Пол')->setSort(30)->setCode('SEX');
            $customFieldsCollection->add($sex);
        }
        if (!$result->getBy('code', 'AGE')) {
            $age = new NumericCustomFieldModel();
            $age->setName('Возраст')->setSort(40)->setCode('AGE');
            $customFieldsCollection->add($age);
        }

        if (isset($sex) || isset($age)) {
            try {
                $customFieldsService->add($customFieldsCollection);
            } catch (AmoCRMApiException $e) {
                var_dump($customFieldsService->getLastRequestInfo());
                die;
            }
        }
    }

    private function link_lead($contact, $apiClient)
    {
        $lead = new LeadModel();
        $now = new DateTime();
        $lead->setName("Сделка {$contact->getFirstName()} {$contact->getLastName()}")
            ->setPrice(54321)
            ->setAccountId($contact->getAccountId())
            ->setCreatedAt($now->getTimestamp());
        $leadModel = $apiClient->leads()->addOne($lead);

        $links = new LinksCollection();
        $links->add($leadModel);
        $apiClient->contacts()->link($contact, $links);

        $this->link_task($leadModel, $apiClient);
        $this->link_product($leadModel, $apiClient);
    }

    private function link_task($lead, $apiClient)
    {
        $task = new TaskModel();

        $now = date("Y-m-d", $lead->getCreatedAt());
        $completeTill = strtotime($now . " +4 days 6 hours");
        $weekday = date("w", $completeTill);
        if ((int) $weekday === 6) {
            $completeTill += 48 * 60 * 60;
        } elseif ((int) $weekday === 0) {
            $completeTill += 24 * 60 * 60;
        }

        $usersCollection = $apiClient->users()->get();

        $task->setTaskTypeId(TaskModel::TASK_TYPE_ID_CALL)
            ->setText('Новая задача')
            ->setCompleteTill($completeTill)
            ->setDuration(9 * 60 * 60)
            ->setEntityType(EntityTypesInterface::LEADS)
            ->setEntityId($lead->getId())
            ->setResponsibleUserId($usersCollection->first()->getId());

        $taskModel = $apiClient->tasks()->addOne($task);
    }

    private function link_product($lead, $apiClient)
    {
        $catalogsCollection = $apiClient->catalogs()->get();
        $catalog = $catalogsCollection->getBy('name', 'Товары');
        $catalogElementsService = $apiClient->catalogElements($catalog->getId());
        $catalogElementsCollection = $catalogElementsService->get();

        $product1 = $catalogElementsCollection->first();
        $product1->setQuantity(1);
        $product2 = $catalogElementsCollection->last();
        $product2->setQuantity(1);

        $links = new LinksCollection();
        $links->add($product1)->add($product2);
        $apiClient->leads()->link($lead, $links);
    }
}
