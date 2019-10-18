Feature: Search list migration
  It should be possible to migrate searches lists.

  Scenario: Migrated list gets bound to user GUID
    Given a known user
    And a migrated search list for legacy user id "the-ouid":
      | title       | query             | last_seen           |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00:00 |
      | Harry       | harry potter      | 2019-10-01 10:00:00 |
    When the user runs migrate for legacy user id "the-ouid"
    When fetching searches should return:
      | title       | query             | last_seen           |
      | Harry       | harry potter      | 2019-10-01 10:00:00 |
      | Onkel Danny | Dan Turèll        | 2019-10-02 09:00:00 |
      | Sightseeing | Hitchhikers Guide | 2019-10-02 10:00:00 |
