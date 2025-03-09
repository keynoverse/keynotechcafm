import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

// Custom metrics
const errors = new Rate('errors');

// Test configuration
export const options = {
    stages: [
        { duration: '1m', target: 50 },  // Ramp up to 50 users over 1 minute
        { duration: '3m', target: 50 },  // Stay at 50 users for 3 minutes
        { duration: '1m', target: 0 },   // Ramp down to 0 users over 1 minute
    ],
    thresholds: {
        'http_req_duration': ['p(95)<500'], // 95% of requests must complete within 500ms
        'errors': ['rate<0.1'],             // Error rate must be less than 10%
    },
};

// Simulated user session data
const users = [
    { email: 'admin@example.com', password: 'password123' },
    { email: 'manager@example.com', password: 'password123' },
    { email: 'technician@example.com', password: 'password123' }
];

// Main test function
export default function() {
    const baseUrl = 'http://localhost:8000/api';
    let token;

    // 1. Login
    {
        const user = users[Math.floor(Math.random() * users.length)];
        const loginRes = http.post(`${baseUrl}/auth/login`, JSON.stringify(user), {
            headers: { 'Content-Type': 'application/json' },
        });

        check(loginRes, {
            'login successful': (r) => r.status === 200,
            'has token': (r) => JSON.parse(r.body).data.token !== undefined,
        }) || errors.add(1);

        token = JSON.parse(loginRes.body).data.token;
        sleep(1);
    }

    // Common headers for authenticated requests
    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    };

    // 2. Get assets list
    {
        const assetsRes = http.get(`${baseUrl}/assets`, { headers });
        check(assetsRes, {
            'get assets successful': (r) => r.status === 200,
            'assets returned': (r) => JSON.parse(r.body).data.length > 0,
        }) || errors.add(1);
        sleep(2);
    }

    // 3. Get work orders list
    {
        const workOrdersRes = http.get(`${baseUrl}/work-orders`, { headers });
        check(workOrdersRes, {
            'get work orders successful': (r) => r.status === 200,
            'work orders returned': (r) => JSON.parse(r.body).data.length > 0,
        }) || errors.add(1);
        sleep(2);
    }

    // 4. Get maintenance schedules
    {
        const schedulesRes = http.get(`${baseUrl}/maintenance-schedules`, { headers });
        check(schedulesRes, {
            'get schedules successful': (r) => r.status === 200,
            'schedules returned': (r) => JSON.parse(r.body).data.length > 0,
        }) || errors.add(1);
        sleep(2);
    }

    // 5. Create work order
    {
        const workOrderData = {
            title: `Test Work Order ${Date.now()}`,
            description: 'Load test work order',
            priority: 'medium',
            asset_id: 1,
            due_date: new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0]
        };

        const createRes = http.post(
            `${baseUrl}/work-orders`,
            JSON.stringify(workOrderData),
            { headers }
        );

        check(createRes, {
            'create work order successful': (r) => r.status === 201,
        }) || errors.add(1);
        sleep(3);
    }

    // 6. Get dashboard statistics
    {
        const statsRes = http.get(`${baseUrl}/dashboard/statistics`, { headers });
        check(statsRes, {
            'get statistics successful': (r) => r.status === 200,
            'statistics returned': (r) => {
                const data = JSON.parse(r.body).data;
                return data.total_assets !== undefined &&
                       data.total_work_orders !== undefined &&
                       data.total_maintenance_schedules !== undefined;
            },
        }) || errors.add(1);
        sleep(2);
    }

    // 7. Search assets
    {
        const searchRes = http.get(`${baseUrl}/assets?search=test`, { headers });
        check(searchRes, {
            'search successful': (r) => r.status === 200,
        }) || errors.add(1);
        sleep(2);
    }

    // 8. Filter work orders
    {
        const filterRes = http.get(`${baseUrl}/work-orders?status=open&priority=high`, { headers });
        check(filterRes, {
            'filter successful': (r) => r.status === 200,
        }) || errors.add(1);
        sleep(2);
    }

    // 9. Get reports
    {
        const reportsRes = http.get(`${baseUrl}/reports`, { headers });
        check(reportsRes, {
            'get reports successful': (r) => r.status === 200,
        }) || errors.add(1);
        sleep(2);
    }

    // 10. Logout
    {
        const logoutRes = http.post(`${baseUrl}/auth/logout`, null, { headers });
        check(logoutRes, {
            'logout successful': (r) => r.status === 200,
        }) || errors.add(1);
    }
} 