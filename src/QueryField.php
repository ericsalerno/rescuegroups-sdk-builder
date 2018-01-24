<?php
/**
 * Class QueryField
 *
 * @package RescueGroupsBuilder
 * @author Eric
 */
namespace RescueGroupsBuilder;

class QueryField
{
    /**
     * @var string
     */
    public $friendlyName;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $sdkFieldName;

    /**
     * @var string
     */
    public $parameterName;

    /**
     * @var string
     */
    public $required = "0";

    /**
     * @var bool
     */
    public $isKey = false;

    /**
     * @var string
     */
    public $constantName;

    /**
     * QueryField constructor.
     * @param $fieldName
     * @param $sdkFieldName
     * @param $fieldData
     */
    public function __construct($fieldName, $sdkFieldName, $fieldData)
    {
        $this->sdkFieldName = $sdkFieldName;
        $this->parameterName = ucfirst($sdkFieldName);

        if (preg_match('#^Set[A-Z]#', $this->parameterName))
        {
            //Remove Set from the parameter name
            $this->parameterName = substr($this->parameterName, 3);
        }

        switch ($fieldData->type)
        {
            case 'key':
                $this->type = 'integer';
                break;

            case 'date':
                $this->type = '\DateTime';
                break;

            case 'datetime':
                $this->type = '\DateTime';
                break;

            case 'decimal':
                $this->type = 'float';
                break;

            default:
                $this->type = 'string';
                break;
        }

        $this->friendlyName = $fieldData->friendlyname;
        $this->name = $fieldData->name;

        if ($fieldData->friendlyname == 'ID' || (!empty($fieldData->properties[0]) && $fieldData->properties[0] == 'required'))
        {
            $this->required = "1";
        }

        if (empty($this->friendlyName))
        {
            $this->friendlyName = $this->sdkFieldName;
        }

        $this->constantName = strtoupper(preg_replace('#([A-Z])#', '_$1', $this->sdkFieldName));
    }

    /**
     * @param $className
     * @param $type
     * @param $fieldName
     * @return string
     */
    static public function getFieldSDKName($className, $type, $fieldName)
    {
        //Create friendly field name
        $sdkFieldName = $fieldName;

        //AnimalQualities is the exception here, the field would be blank because it's name is animalQualities
        if ($className != 'AnimalQualities' && $className != 'EventAnimalAttendance')
            $sdkFieldName = lcfirst(str_replace($type, '', $fieldName));

        return $sdkFieldName;
    }

    /**
     * @var array
     */
    static public $specialFieldMaps = [
        'AnimalBreeds' => 'breed',
        'AnimalColors' => 'color',
        'AnimalGroups' => 'group',
        'AnimalsAdoptions' => 'adoption',
        'AnimalsJournalEntryTypes' => 'journalEntrytype',
        'AnimalsJournalEntries' => 'journalEntry',
        'AnimalsMeetRequests' => 'meetrequest',
        'AnimalPatterns' => 'pattern',
        'AnimalQualities' => 'IGNORE',
        'AnimalsExports' => 'export',
        'AnimalsReasonsEuthanasia' => 'reason',
        'AnimalsReasonsImpound' => 'reason',
        'AnimalsReasonsSurrender' => 'reason',
        'AnimalsReasonsTransfer' => 'reason',
        'AnimalSpecies' => 'species',
        'AnimalStatuses' => 'status',
        'CallsCategories' => 'category',
        'CallsLogEntries' => 'logentry',
        'CallsQueues' => 'queue',
        'CallsQueuesMembers' => 'member',
        'ColoniesCareTakers' => 'caretaker',
        'ContactsGroups' => 'group',
        //'Events-search' => '',
        'EventAnimalAttendance' => 'attendance',
        'IntakesServiceTypes' => 'service',
        'NewsArticles' => 'article',
        'OutcomesReleases' => 'outcomesRelease',
        'VolunteerHours' => 'volunteerHours',
        'VolunteersJournalEntries' => 'journalEntry'
    ];

}
