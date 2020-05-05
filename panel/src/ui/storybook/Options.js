export default (limit = 3, info) => {

  let options = [...Array(limit).keys()].map(index => {

    const num = index + 1;

    return {
      value: num,
      text: "Option " + num
    };
  });

  if (info) {
    options = options.map(option => {
      option.info = "Info for option " + option.text

      return option;
    });
  }

  return options;

};
