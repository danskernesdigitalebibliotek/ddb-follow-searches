Feature: Deleting searches from searches list
  Users should be able to delete searches from their list.

  Scenario: User should be able to delete search
    Given a known user
    And they have the following items on the list:
      | title       | query             | last_seen        |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |
      | Harry       | harry potter      | 2019-10-01 10:00 |
    When deleting the search "Harry" from the searches list
    Then the system should return success
    And fetching the list should return:
      | title       | query             | last_seen        |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00 |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |
