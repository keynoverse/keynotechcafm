import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

// Custom metrics
const errors = new Rate('errors');

// Test configuration for stress testing
export const options = {
    stages: [
        { duration: '2m', target: 100 },   // Ramp up to 100 users over 2 minutes
        { duration: '5m', target: 100 },   // Stay at 100 users for 5 minutes
        { duration: '2m', target: 200 },   // Ramp up to 200 users over 2 minutes
        { duration: '5m', target: 200 },   // Stay at 200 users for 5 minutes
        { duration: '2m', target: 300 },   // Ramp up to 300 users over 2 minutes
        { duration: '5m', target: 300 },   // Stay at 300 users for 5 minutes
        { duration: '2m', target: 0 },     // Ramp down to 0 users over 2 minutes
    ],
    thresholds: {
        'http_req_duration': ['p(95)<2000'],  // 95% of requests must complete within 2s
        'http_req_failed': ['rate<0.2'],      // Error rate must be less than 20%
        errors: ['rate<0.2'],
    },
};

// Simulated user session data
const users = Array.from({ length: 50 }, (_, i) => ({
    email: `user${i}@example.com`,
    password: 'password123'
}));

// Main test function
export default function() {
    const baseUrl = 'http://localhost:8000/api';
    let token;

    // 1. Login with high concurrency
    {
        const user = users[Math.floor(Math.random() * users.length)];
        const loginRes = http.post(`${baseUrl}/auth/login`, JSON.stringify(user), {
            headers: { 'Content-Type': 'application/json' },
        });

        if (!check(loginRes, {
            'login successful': (r) => r.status === 200,
            'has token': (r) => JSON.parse(r.body).data.token !== undefined,
        })) {
            errors.add(1);
            return;
        }

        token = JSON.parse(loginRes.body).data.token;
    }

    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    };

    // 2. Concurrent data fetching
    {
        const requests = {
            'assets': `${baseUrl}/assets`,
            'work_orders': `${baseUrl}/work-orders`,
            'maintenance_schedules': `${baseUrl}/maintenance-schedules`,
            'dashboard': `${baseUrl}/dashboard/statistics`,
            'reports': `${baseUrl}/reports`
        };

        const responses = http.batch(Object.entries(requests).map(([name, url]) => ({
            method: 'GET',
            url,
            headers,
            tags: { name }
        })));

        for (let res of Object.values(responses)) {
            check(res, {
                'status was 200': (r) => r.status === 200,
            }) || errors.add(1);
        }
    }

    // 3. Heavy search and filter operations
    {
        const searchQueries = [
            'test',
            'maintenance',
            'repair',
            'urgent',
            'completed'
        ];

        const filterCombinations = [
            'status=open&priority=high',
            'status=in_progress&priority=medium',
            'type=corrective&status=completed',
            'assigned_to=1&priority=low',
            'due_date=overdue&status=open'
        ];

        // Randomly select queries to simulate real user behavior
        const searchQuery = searchQueries[Math.floor(Math.random() * searchQueries.length)];
        const filterQuery = filterCombinations[Math.floor(Math.random() * filterCombinations.length)];

        const requests = [
            http.get(`${baseUrl}/assets?search=${searchQuery}`, { headers }),
            http.get(`${baseUrl}/work-orders?${filterQuery}`, { headers }),
            http.get(`${baseUrl}/maintenance-schedules?search=${searchQuery}`, { headers })
        ];

        for (let res of requests) {
            check(res, {
                'search/filter successful': (r) => r.status === 200,
            }) || errors.add(1);
        }
    }

    // 4. Concurrent write operations
    {
        const batch = [];
        
        // Create work order
        batch.push({
            method: 'POST',
            url: `${baseUrl}/work-orders`,
            body: JSON.stringify({
                title: `Stress Test Work Order ${Date.now()}`,
                description: 'Created during stress test',
                priority: 'high',
                asset_id: 1,
                due_date: new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0]
            }),
            headers
        });

        // Create maintenance schedule
        batch.push({
            method: 'POST',
            url: `${baseUrl}/maintenance-schedules`,
            body: JSON.stringify({
                title: `Stress Test Schedule ${Date.now()}`,
                description: 'Created during stress test',
                frequency: 30,
                asset_id: 1,
                next_due_date: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0]
            }),
            headers
        });

        const responses = http.batch(batch);
        
        for (let res of responses) {
            check(res, {
                'write operation successful': (r) => r.status === 201,
            }) || errors.add(1);
        }
    }

    // 5. Heavy reporting operations
    {
        const reportTypes = [
            'asset-status',
            'work-order-summary',
            'maintenance-compliance',
            'cost-analysis',
            'performance-metrics'
        ];

        const reportType = reportTypes[Math.floor(Math.random() * reportTypes.length)];
        const reportRes = http.post(`${baseUrl}/reports/generate`, JSON.stringify({
            type: reportType,
            start_date: new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0],
            end_date: new Date().toISOString().split('T')[0],
            format: 'pdf'
        }), { headers });

        check(reportRes, {
            'report generation successful': (r) => r.status === 200,
        }) || errors.add(1);
    }

    // 6. Concurrent file operations
    {
        const fileData = http.file('dummy.pdf', 'application/pdf', 'dummy content');
        const attachmentRes = http.post(`${baseUrl}/attachments`, {
            file: fileData,
            description: 'Stress test attachment'
        }, {
            headers: {
                ...headers,
                'Content-Type': 'multipart/form-data'
            }
        });

        check(attachmentRes, {
            'file upload successful': (r) => r.status === 201,
        }) || errors.add(1);
    }

    // Random sleep between 1-3 seconds to simulate user think time
    sleep(Math.random() * 2 + 1);
}

// Custom setup function to prepare test data
export function setup() {
    // Create test users if needed
    const adminRes = http.post('http://localhost:8000/api/auth/register', JSON.stringify({
        email: 'admin@example.com',
        password: 'password123',
        name: 'Admin User',
        role: 'admin'
    }), {
        headers: { 'Content-Type': 'application/json' }
    });

    return { adminToken: adminRes.status === 201 ? JSON.parse(adminRes.body).data.token : null };
}

// Custom teardown function to clean up test data
export function teardown(data) {
    if (data.adminToken) {
        const headers = {
            'Authorization': `Bearer ${data.adminToken}`,
            'Content-Type': 'application/json'
        };

        // Clean up test data
        http.post('http://localhost:8000/api/test/cleanup', null, { headers });
    }
} 