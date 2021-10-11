import Fiber from "./index";

export default async function (type, query, options = {}) {
  try {
    const data = await Fiber.request("search/" + type, {
      query: {
        query: query
      },
      ...options
    });

    // the GET request for the search is failing
    if (!data.$search) {
      throw `The search could not be executed`;
    }

    // the search sends a backend error
    if (data.$search.error) {
      throw data.$search.error;
    }

    // return the search object if needed
    return data.$search;
  } catch (e) {
    console.error(e);
    this.$store.dispatch("notification/error", e);
  }
}
