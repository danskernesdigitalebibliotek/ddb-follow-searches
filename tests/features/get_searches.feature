Feature: Fetching searches list
  Users should be able to fetch their searches list.

  Scenario: Should return an error if seaches list is not the default one
    Given a known user
    When fetching "other" searches
    Then the system should return not found

  Scenario: Should return an empty searches list if not found
    Given a known user that has no items on searches list
    When fetching searches
    Then the system should return success
    And the searches list should be emtpy

  Scenario: User can fetch their searches list with hitcounts
    Given a known user
    And they have the following items on the list:
      | title       | query             | last_seen           |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00:00 |
      | Harry       | harry potter      | 2019-10-01 10:00:00 |
    And the searches has the following hitcounts:
      | query           | hitcount |
      | Hitchhikers Guide |        3 |
      | Dan Turèll        |        4 |
      | harry potter      |        2 |
    When fetching searches
    Then the system should return success
    And the searches list should contain:
      | title       | query             | last_seen           | hit_count |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |         3 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00:00 |         4 |
      | Harry       | harry potter      | 2019-10-01 10:00:00 |         2 |

  Scenario: The searches list is ordered by creation date, newest first
    Given a known user
    And the time is "2019-10-01 10:00"
    When search "terry pratchett" with title "Terry" is added to the list
    And the time is "2019-10-02 08:00"
    And search "Dan Turèll" with title "Onkel Danny" is added to the list
    And the time is "2019-10-02 09:00"
    And search "Hitchhikers Guide" with title "Sightseeing" is added to the list

    When fetching searches
    Then the system should return success
    And the searches list should contain:
      | title       | query             | last_seen           | hit_count |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 09:00:00 |         0 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 08:00:00 |         0 |
      | Terry       | terry pratchett   | 2019-10-01 10:00:00 |         0 |
