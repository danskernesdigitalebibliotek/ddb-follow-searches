Feature: Searches can be paginated
  The client should be able to fetch pages of searches.

  Scenario Outline: Client should be able to fetch pages of searches.
    Given a known user
    And they have searches from A to Z on their search list
    When fetching the search list page <page>, with a page size of <size>
    Then the system should return success
    And the search list should have searches <names>

    Examples:
      | page | size | names               |
      |    1 |    5 | Z,Y,X,W,V           |
      |    2 |    5 | U,T,S,R,Q,          |
      |    1 |   10 | Z,Y,X,W,V,U,T,S,R,Q |
      |    2 |   10 | P,O,N,M,L,K,J,I,H,G |
      |    3 |   10 | F,E,D,C,B,A         |
      |   10 |    5 |                     |

  Scenario: Client should be able to get full list without paging
    Given a known user
    And they have searches from A to Z on their search list
    When fetching searches
    Then the system should return success
    And the search list should have searches Z,Y,X,W,V,U,T,S,R,Q,P,O,N,M,L,K,J,I,H,G,F,E,D,C,B,A

  Scenario: Invalid page size should throw an error
    Given a known user
    And they have searches from A to Z on their search list
    When fetching the search list page 2, with a page size of banana
    Then the system should return validation error
