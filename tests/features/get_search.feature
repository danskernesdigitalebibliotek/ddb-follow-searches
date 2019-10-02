Feature: Fetching search
  Users should be able to fetch the result of a search.

  Scenario: User can fetch their searches list with hitcounts
    Given a known user
    And the time is "2019-10-02 17:00"
    And they have the following items on the list:
      | title       | search            | last_seen        | with hit_count | with pids      |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00 |              3 | one, two three |
    When they fetches the "Sightseeing" search
    Then the system should return success
    And the search result should be:
      | pids |
      | one  |
      | two  |
      | three |
    And the searches list should contain:
      | title       | search            | last_seen        | hit_count |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 17:00 |         0 |
