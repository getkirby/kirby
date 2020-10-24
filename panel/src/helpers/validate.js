
export default {
  datetime(app, value, limit, condition, base = "day") {
    value = app.$library.dayjs.utc(value);

    if (!limit) {
      return value && value.isValid();
    }

    if (!value || !value.isValid()) {
      return true;
    }

    limit = app.$library.dayjs.utc(limit);
    return value.isSame(limit, base) || value[condition](limit, base);
  }
}
