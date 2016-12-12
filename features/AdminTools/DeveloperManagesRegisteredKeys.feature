Ability: Application developer has ability to manage user keys in Yapeal-ng.
    In order to insure Yapeal-ng retrieves the right Eve APIs
    As an Application Developer
    I need to be able to manage the information in the RegisteredKey table

    Background: I have a database connection
        Given I have an initialized instance of PDO connection
        And I have an new instance of the ManageRegisteredKey class

    Scenario Outline: Create a new registered key
        Given that I have the new key information: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And there is not a keyID = "<keyID>" row in the yapealRegisteredKey table
        When I create the new key in the ManageRegisteredKey class
        Then from the ManageRegisteredKey class I should be able to read back: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And the isDirty flag should be set in ManageRegisteredKey
        But there should still not be a "keyID" = "<keyID>" row in the "yapealRegisteredKey" table

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |

    Scenario Outline: Create a new registered key and successfully commit it
        Given that I have the new key information: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And there is not a keyID = "<keyID>" row in the yapealRegisteredKey table
        When I create the new key in the ManageRegisteredKey class
        And I successfully commit the new key to the table in ManageRegisteredKey
        Then there should now exist a row in the "yapealRegisteredKey" table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And the isDirty flag should be cleared in ManageRegisteredKey

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |

    Scenario Outline: I delete an existing key
        Given there is an existing row in the yapealRegisteredKey table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        When I delete keyID = "<keyID>" in the ManageRegisteredKey class
        Then there should still exist a row in the "yapealRegisteredKey" table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And the isDirty flag should be set in ManageRegisteredKey

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |

    Scenario Outline: I delete an existing key and commit it
        Given there is an existing row in the yapealRegisteredKey table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        When I delete keyID = "<keyID>" in the ManageRegisteredKey class
        And I successfully commit the delete in ManageRegisteredKey
        Then there should no longer be a "keyID" = "<keyID>" row in the "yapealRegisteredKey" table
        And the isDirty flag should be cleared in ManageRegisteredKey

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |

    Scenario Outline: I can read existing registered key from table
        Given there is an existing row in the yapealRegisteredKey table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        Then if I set the refresh flag when reading the same keyID in ManageRegisteredKey I should get: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And the isDirty flag should be cleared in ManageRegisteredKey

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |

    Scenario Outline: I can update individual columns in an existing key
        Given there is an existing row in the yapealRegisteredKey table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"
        And if I set the refresh flag when reading the same keyID in ManageRegisteredKey I get the same data as in the table back.
        Then I can update "changed" = "newValue" in ManageRegisteredKey but the other columns don't change:
            | changed       | newValue |
            | active        | 0        |
            | activeAPIMask | 0        |
            | vCode         | def456   |
        And the isDirty flag should be set in ManageRegisteredKey
        And there should still exist a row in the "yapealRegisteredKey" table containing: "<active>" "<activeAPIMask>" "<keyID>" "<vCode>"

        Examples:
            | active | activeAPIMask | keyID | vCode  |
            | 1      | 1             | 123   | abc123 |
            | 0      | 1             | 123   | abc123 |
            | 1      | 1             | 123   | abc123 |

