import Fiber from "./index";

export default async function (type, query, options = {}) {
  return await Fiber.request("search/" + type, {
    query: {
      query: query
    },
    type: "$search",
    ...options
  });
}
