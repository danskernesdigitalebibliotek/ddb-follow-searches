Feature: Statistics
  Statistics should be added as functionality is used

  Scenario: Add search to list
    Given a known user
    When search "harry potter" with title "Harry" is added to the list
    Then there should be a "add_search" event for the user and a search with the title "Harry"
    And the total count of elements for the event should be "1"
    And the event elements should contain:
      | event       | collectionId | itemId |
      | add_search  | default      | 1      |

  Scenario: Add first search to list
    Given a known user that has no items on searches list
    When search "harry potter" with title "Harry" is added to the list
    Then there should be a "create_list" event for the user
    And the total count of elements for the event should be 1
    Then there should be a "add_search" event for the user and a search with the title "Harry"
    And the total count of elements for the event should be 1

  Scenario: User can fetch their list
    Given a known user
    And they have the following items on the list:
      | title              | query             |
      | Harry              | harry potter      |
      | Superhero          | spiderman         |
    When fetching searches
    Then there should be a "get_list" event for the user

  Scenario: A user can check that a search is on the list
    Given a known user
    And they have the following items on the list:
      | title              | query             |
      | Harry              | harry potter      |
      | Superhero          | spiderman         |
    When they fetch the "Superhero" search
    Then there should be a "check_search" event for the user and a search with the title "Superhero"

  Scenario: A user can check that multiple searches are on the list
    Given a known user
    And they have the following items on the list:
      | title              | query             |
      | Harry              | harry potter      |
      | Superhero          | spiderman         |
    When they fetch the "Superhero" search
    And they fetch the "Harry" search
    Then there should be a "check_search" event for the user and a search with the title "Superhero"
    And there should be a "check_search" event for the user and a search with the title "Harry"

  Scenario: User should be able to delete searches
    Given a known user
    And they have the following items on the list:
      | title              | query             |
      | Harry              | harry potter      |
      | Superhero          | spiderman         |
    When deleting the search "Harry" from the searches list
    Then there should be a "remove_search" event for the user
    And the total count of elements for the event should be 1
    And the event elements should contain:
      | event          | collectionId | itemId |
      | remove_search  | default      | 1      |

  Scenario: User should be able to delete last search on the list
    Given a known user
    And they have the following items on the list:
      | title              | query             |
      | Harry              | harry potter      |
      | Superhero          | spiderman         |
    When deleting the search "Harry" from the searches list
    Then there should be a "remove_search" event for the user
    And the total count of elements for the event should be 1
    When deleting the search "Superhero" from the searches list
    Then there should be a "remove_search" event for the user
    And there should be a "delete_list" event for the user
    And the total count of elements for the event should be 0
