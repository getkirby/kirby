
export default function (array) {
  if (array === undefined) {
    return undefined;
  }

  return JSON.parse(JSON.stringify(array));
}
