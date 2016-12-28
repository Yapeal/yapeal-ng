@suite ConfigManagement
Ability: Yapeal needs to be able to use CRUD methods to manage any given config files
    In order for insure the right settings are used
    As Yapeal-ng
    I need to have a config management class with a CRUD type interface.

    Background: I have a Container class.
        Given I have an empty Container class
        And I have created a new instance of the ConfigManager class

    Scenario: Creating a completely new configuration
        Given I have a config file "yapealDefaults.yaml" that contains:
            """
            ---
            Yapeal:
                consoleAutoExit: true
                consoleCatchExceptions: false
                consoleName: 'Yapeal-ng Console'
                version: '0.6.0-0-gafa3c59'
            ...
            """
        When I use the create() method of the ConfigManager class
        Then I should can find the follows <keys> and their <values> in the Container class:
            | keys                          | values              |
            | Yapeal.consoleAutoExit        | true                |
            | Yapeal.consoleCatchExceptions | false               |
            | Yapeal.consoleName            | 'Yapeal-ng Console' |
            | Yapeal.version                | '0.6.0-0-gafa3c59'  |
