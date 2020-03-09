Feature: Fetching search
  Users should be able to fetch the result of a search.

  Scenario: User can fetch their searches list with hitcounts
    Given a known user
    And the time is "2019-10-02 17:00"
    And they have the following items on the list:
      | title       | query             | last_seen           |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |
    And the searches has the following hitcounts:
      | query             | pids            |
      | Hitchhikers Guide | one, two, three |
    When they fetch the "Sightseeing" search
    Then the system should return success
    And the search result should be:
      | pid  |
      | one  |
      | two  |
      | three |
    And fetching searches should return:
      | title       | query             | last_seen           |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 17:00:00 |

  Scenario: User can fetch search with custom fields
    Given a known user
    And the time is "2019-10-02 17:00"
    And they have the following items on the list:
      | title       | query             | last_seen           |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |
    And the "Hitchhikers Guide" query has the following results:
      | pid   | title                                     | type |
      | one   | Life, the Universe and Everything         | book |
      | two   | The Restaurant at the End of the Universe | book |
      | three | So long and thank for all the fish        | book |
    When they fetch the "Sightseeing" search with fields "title, type"
    Then the system should return success
    And the search result should be:
      | pid   | title                                     | type |
      | one   | Life, the Universe and Everything         | book |
      | two   | The Restaurant at the End of the Universe | book |
      | three | So long and thank for all the fish        | book |
