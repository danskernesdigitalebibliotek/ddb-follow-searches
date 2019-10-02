Feature: Searches can be paginated
  The client should be able to fetch pages of searches.

  Scenario Outline: Client should be able to fetch pages of searches.
    Given a known user
    And they have searches from A to Z on their search list
    When fetching the search list page <page>, with a page size of <size>
    Then the system should return success
    And fetching the searh list should contain <names>

    Examples:
      | page | size | names               |
      |    1 |    5 | A, B, C, D, E       |
      |    2 |    5 | F, G, H, I, J       |
      |    1 |   10 | A,B,C,D,E,F,G,H,I,J |
      |    2 |   10 | K,L,M,N,O,P,Q,R,S,T |
      |    3 |   10 | U,V,W,X,Y,Z         |
      |    4 |    5 |                     |
