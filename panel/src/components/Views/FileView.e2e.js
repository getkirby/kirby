const dialog = () => {
	return cy.get(".k-dialog");
};

describe("FileView", () => {
	beforeEach(() => {
		cy.visit("/env/install/starterkit");
		cy.visit("/env/user/test");
		cy.visit("/env/auth/test");
	});

	describe("Page File", () => {
		beforeEach(() => {
			cy.visit("/panel/pages/photography+trees/files/cheesy-autumn.jpg");
		});

		it("should display correctly", () => {
			// Title
			cy.get("[data-editable] .k-headline").should(
				"contain",
				"cheesy-autumn.jpg"
			);
			cy.get(".k-topbar-breadcrumb a:last-child").should(
				"contain",
				"cheesy-autumn.jpg"
			);

			// Info
			cy.get(".k-file-preview-details").as("info");

			cy.get("@info")
				.find("dl > div:nth-child(1) dt")
				.should("contain", "Template");
			cy.get("@info")
				.find("dl > div:nth-child(1) dd")
				.should("contain", "image");

			cy.get("@info")
				.find("dl > div:nth-child(2) dt")
				.should("contain", "Media Type");
			cy.get("@info")
				.find("dl > div:nth-child(2) dd")
				.should("contain", "image/jpeg");

			cy.get("@info").find("dl > div:nth-child(3) dt").should("contain", "Url");
			cy.get("@info")
				.find("dl > div:nth-child(3) dd")
				.should("contain", "/photography/trees/cheesy-autumn.jpg");

			cy.get("@info")
				.find("dl > div:nth-child(4) dt")
				.should("contain", "Size");
			cy.get("@info").find("dl > div:nth-child(4) dd").should("contain", "KB");

			cy.get("@info")
				.find("dl > div:nth-child(5) dt")
				.should("contain", "Dimensions");
			cy.get("@info")
				.find("dl > div:nth-child(5) dd")
				.should("contain", "933 × 1400 Pixel");

			cy.get("@info")
				.find("dl > div:nth-child(6) dt")
				.should("contain", "Orientation");
			cy.get("@info")
				.find("dl > div:nth-child(6) dd")
				.should("contain", "Portrait");
		});

		it.skip("should have a preview button", () => {
			// Preview Button
			cy.get(
				'.k-header [data-position="left"] > .k-button-group > :nth-child(1)'
			).as("preview");

			cy.get("@preview").should("have.attr", "target", "_blank");
			cy.get("@preview")
				.invoke("attr", "href")
				.should(
					"contain",
					Cypress.config().baseUrl + "/photography/trees/cheesy-autumn.jpg"
				);
		});

		it("should be renamed", () => {
			// open settings
			cy.get("[data-editable] .k-headline").click();

			dialog().find('input[name="name"]').type("trees");
			dialog().submit();

			cy.url().should("contain", "/pages/photography+trees/files/trees.jpg");
		});

		it.skip("should be deleted", () => {
			// open settings
			cy.get(
				".k-header [data-position=left] .k-button-group:first-child :nth-child(2) .k-button"
			).click();
			cy.get(
				".k-header [data-position=left] .k-dropdown-content .k-button:last-child"
			).click();

			dialog().submit();
			cy.url().should(
				"eq",
				Cypress.config().baseUrl + "/panel/pages/photography+trees"
			);
		});
	});
});
