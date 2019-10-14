Feature: Add to searches list
  Users should be able to add searches to their list.

  Scenario: Add search to list
    Given a known user that has no items on list
    And the time is "2019-10-02 10:00"
    When search "harry potter" with title "Harry" is added to the list "default"
    Then the system should return success
    When fetching "default" searches
      | title  | query       | last_seen        |
      | Harry | harry potter | 2019-10-02 10:00 |

  Scenario: Add material to existing list
    Given a known user
    And they have the following items on the list:
      | title       | search            |
      | Harry       | harry potter      |
      | Onkel Danny | Dan Turèll        |
      | Sightseeing | Hitchhikers Guide |
    When search "terry pratchett" with title "Terry" is added to the list
    Then the system should return success
    And search "terry pratchett" should be on the list with title "Terry"

  Scenario: Materials should only be added once
    Given a known user
    And they have the following items on the list:
      | title | search       | last_seen        |
      | Harry | harry potter | 2019-10-02 10:00 |
    And the time is "2019-10-02 10:00"
    When search "harry potter" with title "Harry" is added to the list
    Then the system should return success
    And fetching the list of searches should return:
      | title  | searches    | last_seen        |
      | Harry | harry potter | 2019-10-02 10:00 |
