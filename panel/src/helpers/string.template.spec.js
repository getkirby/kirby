import string from "./string.js";

describe("$helper.string.template", () => {
  const values = {
    title: "Kirby",
    images: [
      { filename: "foo.jpg" },
      { filename: "bar.jpg" },
      { filename: "baz.jpg" }
    ],
    user: {
      email: "bastian@getkirby.com",
      username: "bastian"
    },
    info: ""
  };

  it("should insert values", () => {
    let result = string.template("Hello World!", values);
    expect(result).to.equal("Hello World!");

    result = string.template("{{title}}", values);
    expect(result).to.equal("Kirby");

    result = string.template("{{ title }}", values);
    expect(result).to.equal("Kirby");

    result = string.template("{ title }", values);
    expect(result).to.equal("Kirby");

    result = string.template("{title}", values);
    expect(result).to.equal("Kirby");

    result = string.template("Hello {{title}}!", values);
    expect(result).to.equal("Hello Kirby!");

    result = string.template("{{images.0.filename}}", values);
    expect(result).to.equal("foo.jpg");

    result = string.template("{ images.1.filename}", values);
    expect(result).to.equal("bar.jpg");

    result = string.template("{images.2.filename }", values);
    expect(result).to.equal("baz.jpg");

    result = string.template("{{user.email}}", values);
    expect(result).to.equal("bastian@getkirby.com");

    result = string.template(
      "{{title}} {{ images.1.filename }}, { user.username }:{user.email}",
      values
    );
    expect(result).to.equal("Kirby bar.jpg, bastian:bastian@getkirby.com");

    result = string.template("Counting: {{images.length}}", values);
    expect(result).to.equal("Counting: 3");

    result = string.template("{{ title }}: {{ info }}", values);
    expect(result).to.equal("Kirby: ");
  });

  it("should insert default", () => {
    const values = { a: null, b: null, cc: null };
    let result = string.template("{{notexists}}", values);
    expect(result).to.equal("…");

    result = string.template("{{ notexists.field }}", values);
    expect(result).to.equal("…");

    result = string.template("Filename: {{ images.99.filename }}", values);
    expect(result).to.equal("Filename: …");

    result = string.template(
      "New user { user.notexists } registered now",
      values
    );
    expect(result).to.equal("New user … registered now");
  });
});
