export default async function (type, query, options = {}) {
  return await this.$fiber.request("search/" + type, {
    query: {
      query: query
    },
    type: "$search",
    ...options
  });
}
