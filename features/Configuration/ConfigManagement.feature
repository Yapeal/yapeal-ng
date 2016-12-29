@suite ConfigManagement
Ability: Yapeal needs to be able to use CRUD methods to manage any given config files
    In order for insure the right settings are used
    As Yapeal-ng
    I need to have a config management class with a CRUD type interface.

    Background: I have a Container class.
        Given I have an empty Container class
        And I have created a new instance of the ConfigManager class

    Scenario: Creating a completely new configuration with single config file
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
        When I use the create method of the ConfigManager class
        Then I should find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |

    Scenario: Creating a completely new configuration with multiple config files
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
        And I have a config file "yapeal.yaml" that contains:
            """
            ---
            Yapeal:
                Sql:
                    platform: mysql
            ...
            """
        When I use the create method of the ConfigManager class
        Then I should find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |
            | Yapeal.Sql.platform           |

    Scenario: Updating the current configuration with a new config file
        Given I had a config file "yapealDefaults.yaml" that contained:
            """
            ---
            Yapeal:
                consoleAutoExit: true
                consoleCatchExceptions: false
                consoleName: 'Yapeal-ng Console'
                version: '0.6.0-0-gafa3c59'
            ...
            """
        And I used the create method of the ConfigManager class
        And I could find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |
        And I have another config file "yapeal.yaml" that contains:
            """
            ---
            Yapeal:
                Sql:
                    platform: mysql
            ...
            """
        When I give the path name "yapeal.yaml" parameter to the addConfigFile method
        And I use the update method of the ConfigManager class
        Then I should find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |
            | Yapeal.Sql.platform           |

    Scenario: Deleting the current configuration
        Given I had a config file "yapealDefaults.yaml" that contained:
            """
            ---
            Yapeal:
                consoleAutoExit: true
                consoleCatchExceptions: false
                consoleName: 'Yapeal-ng Console'
                version: '0.6.0-0-gafa3c59'
            ...
            """
        And I used the create method of the ConfigManager class
        And I could find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |
        When I use the delete method of the ConfigManager class
        Then I should not find the follows <keys> in the Container class:
            | keys                          |
            | Yapeal.consoleAutoExit        |
            | Yapeal.consoleCatchExceptions |
            | Yapeal.consoleName            |
            | Yapeal.version                |

