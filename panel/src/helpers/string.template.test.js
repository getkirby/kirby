/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import string from "./string.js";

describe.concurrent("$helper.string.template", () => {
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
    expect(result).toBe("Hello World!");

    result = string.template("{{title}}", values);
    expect(result).toBe("Kirby");

    result = string.template("{{ title }}", values);
    expect(result).toBe("Kirby");

    result = string.template("{ title }", values);
    expect(result).toBe("Kirby");

    result = string.template("{title}", values);
    expect(result).toBe("Kirby");

    result = string.template("Hello {{title}}!", values);
    expect(result).toBe("Hello Kirby!");

    result = string.template("{{images.0.filename}}", values);
    expect(result).toBe("foo.jpg");

    result = string.template("{ images.1.filename}", values);
    expect(result).toBe("bar.jpg");

    result = string.template("{images.2.filename }", values);
    expect(result).toBe("baz.jpg");

    result = string.template("{{user.email}}", values);
    expect(result).toBe("bastian@getkirby.com");

    result = string.template(
      "{{title}} {{ images.1.filename }}, { user.username }:{user.email}",
      values
    );
    expect(result).toBe("Kirby bar.jpg, bastian:bastian@getkirby.com");

    result = string.template("Counting: {{images.length}}", values);
    expect(result).toBe("Counting: 3");

    result = string.template("{{ title }}: {{ info }}", values);
    expect(result).toBe("Kirby: ");
  });

  it("should insert default", () => {
    const values = { a: null, b: null, cc: null };
    let result = string.template("{{notexists}}", values);
    expect(result).toBe("…");

    result = string.template("{{ notexists.field }}", values);
    expect(result).toBe("…");

    result = string.template("Filename: {{ images.99.filename }}", values);
    expect(result).toBe("Filename: …");

    result = string.template(
      "New user { user.notexists } registered now",
      values
    );
    expect(result).toBe("New user … registered now");
  });
});
