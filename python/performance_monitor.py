#!/usr/bin/env python3
"""
Performance Monitor for Dashboard
Monitors memory usage and response times
"""

import psutil
import time
import requests
import json
from datetime import datetime

def get_memory_usage():
    """Get current memory usage"""
    process = psutil.Process()
    memory_info = process.memory_info()
    return {
        'rss': memory_info.rss / 1024 / 1024,  # MB
        'vms': memory_info.vms / 1024 / 1024,  # MB
        'percent': process.memory_percent()
    }

def test_api_endpoint(url, endpoint):
    """Test API endpoint response time"""
    try:
        start_time = time.time()
        response = requests.get(f"{url}{endpoint}", timeout=10)
        end_time = time.time()
        
        return {
            'endpoint': endpoint,
            'status_code': response.status_code,
            'response_time': round((end_time - start_time) * 1000, 2),  # ms
            'success': response.status_code == 200
        }
    except Exception as e:
        return {
            'endpoint': endpoint,
            'error': str(e),
            'success': False
        }

def monitor_dashboard(base_url="http://localhost:5000"):
    """Monitor dashboard performance"""
    print(f"Dashboard Performance Monitor - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    # Memory usage before API calls
    memory_before = get_memory_usage()
    print(f"Memory before API calls: {memory_before['rss']:.2f} MB (RSS)")
    
    # Test API endpoints
    endpoints = [
        '/api/stats',
        '/api/projects', 
        '/api/analytics',
        '/api/contacts'
    ]
    
    results = []
    for endpoint in endpoints:
        result = test_api_endpoint(base_url, endpoint)
        results.append(result)
        print(f"{endpoint}: {result['response_time']}ms" if result['success'] else f"{endpoint}: ERROR - {result.get('error', 'Unknown')}")
    
    # Memory usage after API calls
    memory_after = get_memory_usage()
    print(f"Memory after API calls: {memory_after['rss']:.2f} MB (RSS)")
    print(f"Memory difference: {memory_after['rss'] - memory_before['rss']:.2f} MB")
    
    # Summary
    successful_calls = sum(1 for r in results if r['success'])
    avg_response_time = sum(r['response_time'] for r in results if r['success']) / max(successful_calls, 1)
    
    print(f"\nSummary:")
    print(f"Successful API calls: {successful_calls}/{len(endpoints)}")
    print(f"Average response time: {avg_response_time:.2f}ms")
    print(f"Memory usage: {memory_after['percent']:.1f}% of system memory")
    
    return {
        'timestamp': datetime.now().isoformat(),
        'memory_before': memory_before,
        'memory_after': memory_after,
        'api_results': results,
        'summary': {
            'successful_calls': successful_calls,
            'total_calls': len(endpoints),
            'avg_response_time': avg_response_time
        }
    }

if __name__ == "__main__":
    try:
        result = monitor_dashboard()
        print("\nMonitoring complete!")
    except KeyboardInterrupt:
        print("\nMonitoring stopped by user")
    except Exception as e:
        print(f"Error during monitoring: {e}") 