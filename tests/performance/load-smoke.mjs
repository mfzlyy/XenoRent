const baseUrl = (process.argv[2] ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const requestCount = Number(process.env.LOAD_REQUESTS ?? 20);
const startedAt = performance.now();

const results = await Promise.all(Array.from({ length: requestCount }, async (_, index) => {
    const requestStartedAt = performance.now();
    try {
        const response = await fetch(`${baseUrl}/login`, { redirect: 'manual' });
        return {
            index: index + 1,
            status: response.status,
            durationMs: performance.now() - requestStartedAt,
        };
    } catch (error) {
        return { index: index + 1, status: 0, error: String(error) };
    }
}));

const durations = results.filter((result) => result.durationMs !== undefined)
    .map((result) => result.durationMs)
    .sort((a, b) => a - b);
const failures = results.filter((result) => result.status !== 200);
const percentile95 = durations.length ? durations[Math.ceil(durations.length * 0.95) - 1] : 0;

console.log(`Target: ${baseUrl}/login`);
console.log(`Requests: ${requestCount}; concurrency: ${requestCount}`);
console.log(`HTTP 200: ${results.length - failures.length}; failed/non-200: ${failures.length}`);
console.log(`Wall time: ${Math.round(performance.now() - startedAt)} ms; p95 response: ${Math.round(percentile95)} ms`);

if (failures.length) {
    console.error(JSON.stringify(failures, null, 2));
    process.exitCode = 1;
}
