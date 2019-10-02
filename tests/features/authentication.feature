Feature: Authentication
  Users should be authenticated with a token.

  @wip
  Scenario: Bad token denies access
    Given an unknown user
    When fetching searches
    Then the system should return access denied

  @wip
  Scenario: Proper token gives access
    Given a known user
    When fetching searches
    Then the system should return success
