<?php
/**
 * Class RequestGenerator
 *
 * @package RescueGroupsBuilder
 * @author Eric
 */
namespace RescueGroupsBuilder;

class RequestGenerator
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    private $objectTypes = [
        'affiliates' => 'Affiliates',
        'animals' => 'Animals',
        'animalBreeds' => 'AnimalBreeds',
        'animalColors' => 'AnimalColors',
        'animalConditions' => 'AnimalConditions',
        'animalExportAccounts' => 'AnimalExportAccounts',
        'animalFiles' => 'AnimalFiles',
        'animalGroups' => 'AnimalGroups',
        'animalsAdoptions' => 'AnimalsAdoptions',
        'animalsJournalCategories' => 'AnimalsJournalCategories',
        'animalsJournalEntrytypes' => 'AnimalsJournalEntryTypes',
        'animalsJournalEntries' => 'AnimalsJournalEntries',
        //'animalsMedia' => 'AnimalsMedia',
        'animalsMeetrequests' => 'AnimalsMeetRequests',
        'animalPatterns' => 'AnimalPatterns',
        'animalQualities' => 'AnimalQualities',
        'animalsExports' => 'AnimalsExports',
        'animalsReasonsEuthanasia' => 'AnimalsReasonsEuthanasia',
        'animalsReasonsImpound' => 'AnimalsReasonsImpound',
        'animalsReasonsSurrender' => 'AnimalsReasonsSurrender',
        'animalsReasonsTransfer' => 'AnimalsReasonsTransfer',
        'animalSpecies' => 'AnimalSpecies',
        'animalStatuses' => 'AnimalStatuses',
        'animalAdoptionLeads' => 'AnimalAdoptionLeads',
        'animalAdoptionStatuses' => 'AnimalAdoptionStatuses',
        'calls' => 'Calls',
        'callsCategories' => 'CallsCategories',
        'callsLogentries' => 'CallsLogEntries',
        'callsOutcomes' => 'CallsOutcomes',
        'callsQueues' => 'CallsQueues',
        'callsQueuesMembers' => 'CallsQueuesMembers',
        'callsStatuses' => 'CallsStatuses',
        'callsUrgencies' => 'CallsUrgencies',
        'colonies' => 'Colonies',
        'coloniesCaretakers' => 'ColoniesCareTakers',
        'contacts' => 'Contacts',
        'contactFiles' => 'ContactFiles',
        'contactsGroups' => 'ContactsGroups',
        'countries' => 'Countries',
        'donations' => 'Donations',
        'events' => 'Events',
        'eventanimalattendance' => 'EventAnimalAttendance',
        'intakes' => 'Intakes',
        'intakesBorninrescueshelter' => 'IntakesBornInRescueShelter',
        'intakesImpounds' => 'IntakesImpounds',
        'intakesOwnerrequestedeuthanasias' => 'IntakesOwnerRequestedEuthanasias',
        'intakesOwnersurrenders' => 'IntakesOwnerSurrenders',
        'intakesServices' => 'IntakesServices',
        'intakesStraydropoffs' => 'IntakesStrayDropoffs',
        'intakesStraypickups' => 'IntakesStrayPickups',
        'intakesTransfers' => 'IntakesTransfers',
        'intakesServicetypes' => 'IntakesServiceTypes',
        'inventoryitems' => 'InventoryItems',
        'inventoryitemsConditions' => 'InventoryItemsConditions',
        'inventoryfiles' => 'InventoryFiles',
        'inventoryLoaners' => 'InventoryLoaners',
        'locations' => 'Locations',
        'memorials' => 'Memorials',
        'microchipRegistrations' => 'MicrochipRegistrations',
        'microchipVendors' => 'MicrochipVendors',
        'newsarticles' => 'NewsArticles',
        'orgs' => 'Orgs',
        'outcomes' => 'Outcomes',
        'outcomesAdoptions' => 'OutcomesAdoptions',
        'outcomesDeceased' => 'OutcomesDeceased',
        'outcomesEuthanasias' => 'OutcomesEuthanasias',
        'outcomesReleases' => 'OutcomesReleases',
        'outcomesReturntoowner' => 'OutcomesReturnToOwner',
        'outcomesTransfers' => 'OutcomesTransfers',
        'partnerships' => 'Partnerships',
        'roles' => 'Roles',
        'submittedforms' => 'SubmittedForms',
        'settings' => 'Settings',
        'testimonials' => 'Testimonials',
        'users' => 'Users',
        'volunteerHours' => 'VolunteerHours',
        'volunteersJournalEntries' => 'VolunteersJournalEntries',
        'waitinglists' => 'WaitingLists',
        'webfiles' => 'WebFiles',
        'webimages' => 'WebImages',
        'webpages' => 'WebPages',
        'website' => 'Website',
    ];

    /**
     * @var \Mustache_Engine
     */
    private $mustache;

    /**
     * Call define and build a returnable datamodel
     */
    public function buildDefinableDataModel()
    {
        //Instantiate
        $api = new \RescueGroups\API();
        $api->setSandboxMode(true);

        $this->mustache = new \Mustache_Engine();

        //Login but use vcr
        $vcr = \Dshafik\GuzzleHttp\VcrHandler::turnOn(__DIR__ . '/../../tests/data/fixtures/action-login.json');
        $api->setCustomGuzzleHandler($vcr);

        $login = new \RescueGroups\Request\Actions\Login();
        $api->executeRequest($login);

        $queryDocLinks = '';

        //Check definitions with vcr for data
        foreach ($this->objectTypes as $type => $className)
        {
            //$className = ucfirst($type);

            $vcr = \Dshafik\GuzzleHttp\VcrHandler::turnOn(__DIR__ . '/../../tests/data/fixtures/define-'.$className.'.json');
            $api->setCustomGuzzleHandler($vcr);

            $fullClassName = '\RescueGroups\Request\Objects\\' . $className . '\Define';

            $query = new $fullClassName();

            try
            {
                $result = $api->executeRequest($query);

                $objectQueries = $this->buildDefinedObjectQueryObjects($className, $type, $result->data);

                foreach ($objectQueries->requests as $object)
                {
                    $this->outputQueryRequestClass($object);
                    $this->outputQueryRequestTestClass($object);
                }

                $this->outputQueryDocumentation($objectQueries);

                $queryDocLinks .= ' * [' . $className . '](' . $className . '/readme.md)' . "\n";
            }
            catch(\Exception $e)
            {
                echo "Skipping " . $className . " due to " . $e->getMessage() . "\n";
                continue;
            }
        }

        $mainDocData = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/documentation-index.mustache'), ['date'=>date(static::DATE_FORMAT), 'queryLinks'=>$queryDocLinks]);

        file_put_contents(__DIR__ . '/../../doc/request/readme.md', $mainDocData);
    }

    /**
     * Gather data first
     *
     * @param $className
     * @param $type
     * @param $definition
     * @return QueryRequest[]
     */
    private function buildDefinedObjectQueryObjects($className, $type, $definition)
    {
        $output = new \stdClass;
        $output->className = $className;
        $output->typeName = $type;
        $output->requests = [];

        foreach ($definition as $request => $requestData)
        {
            $queryRequest = new QueryRequest($className, $type, $request, $requestData);

            $output->requests[] = $queryRequest;

            if ($queryRequest->requestClassName == 'Edit')
            {
                $this->outputResponseClass($queryRequest);
            }

            if ($queryRequest->isParameterAdd())
            {
                $this->outputParameterAddClass($queryRequest);
            }
        }

        return $output;
    }

    /**
     * Output Response Class
     *
     * @param QueryRequest $query
     */
    private function outputResponseClass(QueryRequest $query)
    {
        if ($query->requestClassName != 'Edit') return;

        $responseObject = __DIR__ . '/../../src/Objects/' . $query->responseClassName . '.php';

        $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/response-object.mustache'), $query);

        file_put_contents($responseObject, $data);
    }

    /**
     * Output parameter add class
     *
     * @param QueryRequest $query
     */
    private function outputParameterAddClass(QueryRequest $query)
    {
        $responseObject = __DIR__ . '/../../src/Objects/Create/' . $query->responseClassName . '.php';

        $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/create-object.mustache'), $query);

        file_put_contents($responseObject, $data);
    }

    /**
     * Output the query request class file
     *
     * @param QueryRequest $query
     */
    private function outputQueryRequestClass(QueryRequest $query)
    {
        $dir = __DIR__ . '/../../src/Request/Objects/' . $query->className;

        if (!is_dir($dir))
        {
            mkdir($dir);
        }

        $requestFileName = $dir . '/' . $query->requestClassName . '.php';

        if ($query->isSearch())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/search-query.mustache'), $query);
        }
        elseif ($query->isList())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/list-query.mustache'), $query);
        }
        elseif ($query->isEdit())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/edit-query.mustache'), $query);
        }
        elseif ($query->isAdd())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/add-query.mustache'), $query);
        }
        elseif ($query->isParameterAdd())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/add-create-query.mustache'), $query);
        }
        elseif ($query->isDefine())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/define-query.mustache'), $query);
        }
        elseif ($query->isRegular())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/parameters-query.mustache'), $query);
        }


        file_put_contents($requestFileName, $data);
    }

    /**
     * @param QueryRequest $query
     */
    private function outputQueryRequestTestClass(QueryRequest $query)
    {
        $dir = __DIR__ . '/../../tests/Request/Objects/' . $query->className;

        if (!is_dir($dir))
        {
            mkdir($dir);
        }

        $requestFileName = $dir . '/' . $query->requestClassName . 'Test.php';

        if ($query->isSearch())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/search-query-test.mustache'), $query);
        }
        elseif ($query->isList())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/list-query-test.mustache'), $query);
        }
        elseif ($query->isEdit())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/edit-query-test.mustache'), $query);
        }
        elseif ($query->isAdd())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/add-query-test.mustache'), $query);
        }
        elseif ($query->isParameterAdd())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/add-create-query-test.mustache'), $query);
        }
        elseif ($query->isDefine())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/define-query-test.mustache'), $query);
        }
        elseif ($query->isRegular() || $query->isList())
        {
            $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/parameters-query-test.mustache'), $query);
        }

        file_put_contents($requestFileName, $data);
    }

    /**
     * Output documentation for a set of queries
     *
     * @param $queries
     */
    private function outputQueryDocumentation($queries)
    {
        $docDir = __DIR__ . '/../../doc/request/' . $queries->className;

        if (!is_dir($docDir))
        {
            mkdir($docDir);
        }

        $docFile = $docDir . '/readme.md';

        $data = $this->mustache->render(file_get_contents(__DIR__ . '/new-templates/object-query-docs.mustache'), $queries);

        file_put_contents($docFile, $data);
    }
}
