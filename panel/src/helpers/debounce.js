/**
 * Debounces the callback function
 *
 * @param {Function} fn callback functions
 * @param {int} delay miliseconds to debounce fn calls
 */
export default (fn, delay) => {
  let timer = null;
  return function () {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, arguments), delay);
  };
};
