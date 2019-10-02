Feature: Deleting searches from searches list
  Users should be able to delete searches from their list.

  Scenario: User should be able to delete search
    Given a known user
    And they have the following items on the list:
      | title       | search            | last_seen        | with hit_count |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |              3 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |              4 |
      | Harry       | harry potter      | 2019-10-01 10:00 |              2 |
    When deleting the search "Harry" from the searches list
    Then the system should return success
    And fetching the list should return:
      | title       | search            | last_seen        | hit_count |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |         4 |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |         3 |
