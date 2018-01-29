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
     * @var string
     */
    private $projectDirectory = '';

    /**
     * @var string
     */
    private $packageDirectory = '';

    /**
     * @var array
     */
    static private $templateCache = [];

    /**
     * RequestGenerator constructor.
     */
    public function __construct()
    {
        $this->packageDirectory = __DIR__ . '/..';
        $this->projectDirectory = __DIR__ . '/../../../..';
    }

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
        $vcr = \Dshafik\GuzzleHttp\VcrHandler::turnOn($this->projectDirectory . '/tests/data/fixtures/action-login.json');
        $api->setCustomGuzzleHandler($vcr);

        $login = new \RescueGroups\Request\Actions\Login();
        $api->executeRequest($login);

        $queryDocLinks = '';

        //Check definitions with vcr for data
        foreach ($this->objectTypes as $type => $className)
        {
            //$className = ucfirst($type);

            $vcr = \Dshafik\GuzzleHttp\VcrHandler::turnOn($this->projectDirectory .  '/tests/data/fixtures/define-'.$className.'.json');
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

        $mainDocData = $this->mustache->render($this->getTemplate('documentation-index.mustache'), ['date'=>date(static::DATE_FORMAT), 'queryLinks'=>$queryDocLinks]);

        file_put_contents($this->projectDirectory . '/doc/request/readme.md', $mainDocData);
    }

    /**
     * Gather data first
     *
     * @param $className
     * @param $type
     * @param $definition
     * @return \stdClass
     */
    private function buildDefinedObjectQueryObjects($className, $type, $definition)
    {
        $output = new \stdClass;
        $output->className = $className;
        $output->typeName = $type;
        $output->requests = [];

        $editRequest = $queryRequest = null;

        foreach ($definition as $request => $requestData)
        {
            $queryRequest = new QueryRequest($className, $type, $request, $requestData);

            $output->requests[] = $queryRequest;

            if ($queryRequest->requestClassName == 'Edit')
            {
                $editRequest = clone $queryRequest;
            }

            if ($queryRequest->requestClassName == 'Search')
            {
                $searchRequest = clone $queryRequest;
            }

            if ($queryRequest->isParameterAdd())
            {
                $this->outputParameterAddClass($queryRequest);
            }
        }

        // Get diff of fields between search and edit, then extend the search from the edit
        if (!empty($editRequest) && !empty($searchRequest))
        {
            $hasFields = [];
            foreach ($editRequest->fields as $index => $field)
            {
                $hasFields[$field->name] = true;
            }

            foreach ($searchRequest->fields as $index => $field)
            {
                if (!empty($hasFields[$field->name]))
                {
                    $searchRequest->searchCanExtendEdit = true;
                }
                else
                {
                    $searchRequest->diffFields[] = $field;
                }
            }

            if (empty($searchRequest->diffFields)) $searchRequest->diffFields = $searchRequest->fields;
        }

        if (!empty($editRequest)) $this->outputResponseClass($editRequest);
        if (!empty($searchRequest) && $searchRequest->searchCanExtendEdit) $this->outputSearchResponseClass($searchRequest);

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

        $responseObject = $this->projectDirectory . '/src/Objects/' . $query->responseClassName . '.php';

        $data = $this->mustache->render($this->getTemplate('response-object.mustache'), $query);

        file_put_contents($responseObject, $data);
    }

    /**
     * Output search response class
     *
     * @param QueryRequest $query
     */
    private function outputSearchResponseClass(QueryRequest $query)
    {
        if ($query->requestClassName != 'Search') return;

        $responseObject = $this->projectDirectory . '/src/Objects/Search/' . $query->responseClassName . '.php';

        $data = $this->mustache->render($this->getTemplate('search-response-object.mustache'), $query);

        file_put_contents($responseObject, $data);
    }

    /**
     * Output parameter add class
     *
     * @param QueryRequest $query
     */
    private function outputParameterAddClass(QueryRequest $query)
    {
        $responseObject = $this->projectDirectory . '/src/Objects/Create/' . $query->responseClassName . '.php';

        $data = $this->mustache->render($this->getTemplate('create-object.mustache'), $query);

        file_put_contents($responseObject, $data);
    }

    /**
     * Output the query request class file
     *
     * @param QueryRequest $query
     */
    private function outputQueryRequestClass(QueryRequest $query)
    {
        $dir = $this->projectDirectory . '/src/Request/Objects/' . $query->className;

        if (!is_dir($dir))
        {
            mkdir($dir);
        }

        $requestFileName = $dir . '/' . $query->requestClassName . '.php';

        if ($query->isSearch())
        {
            $data = $this->mustache->render($this->getTemplate('search-query.mustache'), $query);
        }
        elseif ($query->isList())
        {
            $data = $this->mustache->render($this->getTemplate('list-query.mustache'), $query);
        }
        elseif ($query->isEdit())
        {
            $data = $this->mustache->render($this->getTemplate('edit-query.mustache'), $query);
        }
        elseif ($query->isAdd())
        {
            $data = $this->mustache->render($this->getTemplate('add-query.mustache'), $query);
        }
        elseif ($query->isParameterAdd())
        {
            $data = $this->mustache->render($this->getTemplate('add-create-query.mustache'), $query);
        }
        elseif ($query->isDefine())
        {
            $data = $this->mustache->render($this->getTemplate('define-query.mustache'), $query);
        }
        elseif ($query->isRegular())
        {
            $data = $this->mustache->render($this->getTemplate('parameters-query.mustache'), $query);
        }


        file_put_contents($requestFileName, $data);
    }

    /**
     * @param QueryRequest $query
     */
    private function outputQueryRequestTestClass(QueryRequest $query)
    {
        $dir = $this->projectDirectory . '/tests/Request/Objects/' . $query->className;

        if (!is_dir($dir))
        {
            mkdir($dir);
        }

        $requestFileName = $dir . '/' . $query->requestClassName . 'Test.php';

        if ($query->isSearch())
        {
            $data = $this->mustache->render($this->getTemplate('search-query-test.mustache'), $query);
        }
        elseif ($query->isList())
        {
            $data = $this->mustache->render($this->getTemplate('list-query-test.mustache'), $query);
        }
        elseif ($query->isEdit())
        {
            $data = $this->mustache->render($this->getTemplate('edit-query-test.mustache'), $query);
        }
        elseif ($query->isAdd())
        {
            $data = $this->mustache->render($this->getTemplate('add-query-test.mustache'), $query);
        }
        elseif ($query->isParameterAdd())
        {
            $data = $this->mustache->render($this->getTemplate('add-create-query-test.mustache'), $query);
        }
        elseif ($query->isDefine())
        {
            $data = $this->mustache->render($this->getTemplate('define-query-test.mustache'), $query);
        }
        elseif ($query->isRegular() || $query->isList())
        {
            $data = $this->mustache->render($this->getTemplate('parameters-query-test.mustache'), $query);
        }

        file_put_contents($requestFileName, $data);
    }

    /**
     * Get template
     *
     * @param $templateName
     * @return mixed
     */
    private function getTemplate($templateName)
    {
        if (!empty(static::$templateCache[$templateName]))
        {
            return static::$templateCache[$templateName];
        }

        static::$templateCache[$templateName] = file_get_contents($this->packageDirectory . '/resources/templates/' . $templateName);

        return static::$templateCache[$templateName];
    }

    /**
     * Output documentation for a set of queries
     *
     * @param $queries
     */
    private function outputQueryDocumentation($queries)
    {
        $docDir = $this->projectDirectory . '/doc/request/' . $queries->className;

        if (!is_dir($docDir))
        {
            mkdir($docDir);
        }

        $docFile = $docDir . '/readme.md';

        $data = $this->mustache->render($this->getTemplate('object-query-docs.mustache'), $queries);

        file_put_contents($docFile, $data);
    }
}
