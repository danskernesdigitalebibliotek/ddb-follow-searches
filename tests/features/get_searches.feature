Feature: Fetching searches list
  Users should be able to fetch their searches list.

  Scenario: Should return an error if seaches list is not the default one
    Given a known user
    When fetching the "other" searches list
    Then the system should return not found

  Scenario: Should return an empty searches list if not found
    Given a known user that has no items on searches list
    When fetching the seaches list
    Then the system should return success
    And the searches list should be emtpy

  Scenario: User can fetch their searches list with hitcounts
    Given a known user
    And they have the following items on the list:
      | title       | search            | last_seen        | with hit_count |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |              3 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |              4 |
      | Harry       | harry potter      | 2019-10-01 10:00 |              2 |

    When fetching the search list
    Then the system should return success
    And the searches list should contain:
      | title       | search            | last_seen        | hit_count |
      | Harry       | harry potter      | 2019-10-01 10:00 |         2 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |         4 |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |         3 |

  Scenario: The searches list is ordered by creation date, newest first
    Given a known user
    And the time is "2019-10-02 10:00"
    When search "terry pratchett" with title "Terry" is added to the list
    And the time is "2019-10-02 08:00"
    And search "Dan Turèll" with title "Onkel Danny" is added to the list
    And the time is "2019-10-02 09:00"
    And search "Hitchhikers Guide" with title "Sightseeing" is added to the list

    When fetching the search list
    Then the system should return success
    And the searches list should contain:
      | title       | search            | last_seen        | hit_count |
      | Terry       | terry pratchett   | 2019-10-01 10:00 |         2 |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 09:00 |         3 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 08:00 |         4 |
