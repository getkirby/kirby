
export default {
  datetime(app, value, limit, condition, base = "day") {
    let dt = app.$library.dayjs.utc(value);
    
    if (!dt.isValid()) {
      dt = app.$library.dayjs.utc(value, "HH:mm:ss");
    }

    if (!limit) {
      return value && dt.isValid();
    }

    if (!value || !dt.isValid()) {
      return true;
    }

    limit = app.$library.dayjs.utc(limit);
    return dt.isSame(limit, base) || dt[condition](limit, base);
  }
}
