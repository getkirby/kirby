import natsort from '@/helpers/Sort.js'

describe("Sort Helper", () => {

  it("sort", () => {

    let users = [
      { username: "Homer" },
      { username: "Marge" },
      { username: "Bart" },
      { username: "Lisa" },
      { username: "Maggie" }
    ];

    const expected = [
      { username: "Bart" },
      { username: "Homer" },
      { username: "Lisa" },
      { username: "Maggie" },
      { username: "Marge" },
    ];

    const sorter = natsort();

    users.sort((a, b) => {
      return sorter(a.username, b.username);
    });

    expect(users).toEqual(expected);
  });

});
