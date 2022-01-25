describe("DateField", () => {
  before(() => {
    cy.visit("/env/install/fields");
    cy.visit("/env/user/test");
  });

  beforeEach(() => {
    cy.visit("/env/auth/test");
    cy.visit("/panel/pages/date");
  });

  describe("date", () => {
    it("should display correctly", () => {
      // Default Placeholder
      cy.get(".k-field-name-date input")
        .invoke("attr", "placeholder")
        .should("eq", "YYYY-MM-DD");
    });

    it("should keep input value", () => {
      cy.get(".k-field-name-date input")
        .type("2021-05-12")
        .should("have.value", "2021-05-12");
    });

    it("should parse input on enter", () => {
      cy.get(".k-field-name-date input")
        .type("12.5.2021{enter}")
        .should("have.value", "2021-05-12");
    });

    // TODO: does not pass in CI yet for some unknown reason
    // it("should have working calendar dropdown", () => {
    //   cy.get(".k-field-name-date").as("field");
    //   cy.get("@field").find("input").type("2021-05-12");
    //   cy.get("@field").find("button").click();
    //   cy.get("@field").find(".k-calendar-input").as("calendar");
    //   cy.get("@calendar")
    //     .find(".k-calendar-selects .k-select-input:first-child")
    //     .should("contain", "May");
    //   cy.get("@calendar")
    //     .find(".k-calendar-selects .k-select-input:last-child")
    //     .should("contain", "2021");
    //   cy.get("@calendar").find("[aria-selected]").should("contain", "12");
    //   cy.get("@calendar").find(".k-calendar-day button").first().click();
    //   cy.get("@field").find("input").should("have.value", "2021-05-01");
    // });

    it("should have custom display placeholder", () => {
      cy.get(".k-field-name-display input")
        .invoke("attr", "placeholder")
        .should("eq", "DD.MM.YYYY");
    });
  });

  describe("date & time", () => {
    it("should display correctly", () => {
      cy.get(".k-field-name-datetime").as("field");
      cy.get("@field").find(".k-input").first().as("date");
      cy.get("@field").find(".k-input").last().as("time");
      cy.get("@date")
        .find("input")
        .invoke("attr", "placeholder")
        .should("eq", "YYYY-MM-DD");
      cy.get("@time")
        .find("input")
        .invoke("attr", "placeholder")
        .should("eq", "HH:mm");
    });

    it("should display notation correctly", () => {
      cy.get(".k-field-name-timenotation .k-input:last-of-type input")
        .invoke("attr", "placeholder")
        .should("eq", "hh:mm a");
    });

    it("should have working times dropdown", () => {
      cy.get(".k-field-name-datetime").as("field");
      cy.get("@field").find(".k-input").first().as("date");
      cy.get("@field").find(".k-input").last().as("time");
      cy.get("@time").find("button").click();
      cy.get("@time").find(".k-times").as("times");
      cy.get("@times").find("button").first().click();
      cy.get("@time").find("input").should("have.value", "06:00");
    });
  });
});
