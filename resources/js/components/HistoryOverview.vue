<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">📊 History Overview</h1>
      <p class="text-gray-600">Monitor agent performance and business insights</p>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
          <select v-model="selectedDateRange" @change="fetchData" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_7_days">Last 7 Days</option>
            <option value="last_30_days">Last 30 Days</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        
        <div v-if="selectedDateRange === 'custom'" class="flex gap-2">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
            <input v-model="customDateFrom" @change="fetchData" type="date" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
            <input v-model="customDateTo" @change="fetchData" type="date" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <div class="ml-auto">
          <button @click="fetchData" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <span v-if="loading">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>🔄</span>
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Actions</p>
            <p class="text-2xl font-bold text-gray-900">{{ analytics.totalActions || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Active Agents</p>
            <p class="text-2xl font-bold text-gray-900">{{ analytics.activeAgents || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100">
            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Orders Today</p>
            <p class="text-2xl font-bold text-gray-900">{{ analytics.ordersToday || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100">
            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Avg Actions/Agent</p>
            <p class="text-2xl font-bold text-gray-900">{{ analytics.avgActionsPerAgent || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Activity Timeline Chart -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Activity Timeline</h3>
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        <div v-else class="h-64">
          <div v-if="!activityData || activityData.length === 0" class="flex justify-center items-center h-full text-gray-500">
            <div class="text-center">
              <p>No activity data available</p>
              <p class="text-sm mt-1">Data count: {{ activityData?.length || 0 }}</p>
            </div>
          </div>
          <canvas v-else ref="activityChart" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Action Types Distribution -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Action Types</h3>
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        <div v-else class="h-64">
          <div v-if="!actionTypesData || actionTypesData.length === 0" class="flex justify-center items-center h-full text-gray-500">
            <div class="text-center">
              <p>No action types data available</p>
              <p class="text-sm mt-1">Data count: {{ actionTypesData?.length || 0 }}</p>
            </div>
          </div>
          <canvas v-else ref="actionTypesChart" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>

    <!-- Agent Performance -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">👥 Agent Performance</h3>
        <div class="flex gap-2">
          <button @click="sortAgents('actions')" :class="['px-3 py-1 rounded text-sm', agentSort === 'actions' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
            By Actions
          </button>
          <button @click="sortAgents('efficiency')" :class="['px-3 py-1 rounded text-sm', agentSort === 'efficiency' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
            By Efficiency
          </button>
        </div>
      </div>
      
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-700">Agent</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Total Actions</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Orders Created</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Orders Updated</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Success Rate</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Status</th>
              <th class="text-center py-3 px-4 font-medium text-gray-700">Recommendation</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="agent in sortedAgents" :key="agent.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                    <span class="text-sm font-medium text-gray-700">{{ agent.name.charAt(0).toUpperCase() }}</span>
                  </div>
                  <span class="font-medium text-gray-900">{{ agent.name }}</span>
                </div>
              </td>
              <td class="text-center py-3 px-4 text-gray-900">{{ agent.totalActions }}</td>
              <td class="text-center py-3 px-4 text-gray-900">{{ agent.ordersCreated }}</td>
              <td class="text-center py-3 px-4 text-gray-900">{{ agent.ordersUpdated }}</td>
              <td class="text-center py-3 px-4">
                <span :class="getSuccessRateClass(agent.successRate)">{{ agent.successRate }}%</span>
              </td>
              <td class="text-center py-3 px-4">
                <span :class="getPerformanceStatusClass(agent.performance)">{{ agent.performance }}</span>
              </td>
              <td class="text-center py-3 px-4">
                <span class="text-sm text-gray-600">{{ agent.recommendation }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Individual Agent Analysis -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">🕵️ Individual Agent Analysis</h3>
        <div class="flex gap-3">
          <select v-model="selectedAgentId" @change="fetchAgentData" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select an Agent</option>
            <option v-for="agent in availableAgents" :key="agent.id" :value="agent.id">
              {{ agent.name }}
            </option>
          </select>
          <select v-model="agentAnalysisPeriod" @change="fetchAgentData" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="last_7_days">Last 7 Days</option>
            <option value="last_30_days">Last 30 Days</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
          </select>
          <button @click="fetchAgentData" :disabled="!selectedAgentId || loadingAgentData" 
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <span v-if="loadingAgentData">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>📊</span>
            Analyze
          </button>
        </div>
      </div>

      <div v-if="!selectedAgentId" class="text-center py-16 text-gray-500">
        <div class="mb-4">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
        <h4 class="text-lg font-medium text-gray-900 mb-2">Select an Agent to Analyze</h4>
        <p class="text-gray-600">Choose an agent from the dropdown above to see detailed performance analysis</p>
      </div>

      <div v-else-if="loadingAgentData" class="flex justify-center py-16">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-600">Analyzing {{ selectedAgent?.name }}'s performance...</p>
        </div>
      </div>

      <div v-else-if="agentData" class="space-y-6">
        <!-- Agent Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
            <div class="flex items-center">
              <div class="p-2 bg-blue-600 rounded-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-blue-600">Total Actions</p>
                <p class="text-2xl font-bold text-blue-900">{{ agentData.overview.total_actions }}</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
            <div class="flex items-center">
              <div class="p-2 bg-green-600 rounded-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-green-600">Orders Handled</p>
                <p class="text-2xl font-bold text-green-900">{{ agentData.overview.orders_handled }}</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
            <div class="flex items-center">
              <div class="p-2 bg-purple-600 rounded-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-600">Avg Response</p>
                <p class="text-2xl font-bold text-purple-900">{{ agentData.overview.avg_response_time }}</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-600 rounded-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-yellow-600">Success Rate</p>
                <p class="text-2xl font-bold text-yellow-900">{{ agentData.overview.success_rate }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Daily Activity Chart -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">📈 Daily Activity</h4>
            <div class="h-64">
              <canvas ref="agentActivityChart" class="w-full h-full" v-show="agentData.daily_activity && agentData.daily_activity.length > 0"></canvas>
              <div v-show="!agentData.daily_activity || agentData.daily_activity.length === 0" class="flex justify-center items-center h-full text-gray-500">
                <div class="text-center">
                  <p>No activity data available</p>
                  <p class="text-sm mt-1">Data: {{ agentData?.daily_activity?.length || 0 }} items</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Metrics Chart -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">🎯 Performance Breakdown</h4>
            <div class="h-64">
              <canvas ref="agentPerformanceChart" class="w-full h-full" v-show="agentData.performance_metrics && agentData.performance_metrics.length > 0"></canvas>
              <div v-show="!agentData.performance_metrics || agentData.performance_metrics.length === 0" class="flex justify-center items-center h-full text-gray-500">
                <div class="text-center">
                  <p>No performance data available</p>
                  <p class="text-sm mt-1">Data: {{ agentData?.performance_metrics?.length || 0 }} items</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Response Time Trend -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">⏱️ Response Time Trend</h4>
          <div class="h-64">
            <canvas ref="agentResponseChart" class="w-full h-full" v-show="agentData.response_trend && agentData.response_trend.length > 0"></canvas>
            <div v-show="!agentData.response_trend || agentData.response_trend.length === 0" class="flex justify-center items-center h-full text-gray-500">
              <div class="text-center">
                <p>No response time data available</p>
                <p class="text-sm mt-1">Data: {{ agentData?.response_trend?.length || 0 }} items</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Agent Assessment -->
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">🎖️ Agent Assessment</h4>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Strengths -->
            <div>
              <h5 class="font-medium text-green-700 mb-3 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Strengths
              </h5>
              <ul class="space-y-2">
                <li v-for="strength in agentData.assessment.strengths" :key="strength" 
                    class="flex items-start text-sm text-gray-700">
                  <span class="text-green-500 mr-2">•</span>
                  {{ strength }}
                </li>
              </ul>
            </div>

            <!-- Areas for Improvement -->
            <div>
              <h5 class="font-medium text-orange-700 mb-3 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Areas for Improvement
              </h5>
              <ul class="space-y-2">
                <li v-for="improvement in agentData.assessment.improvements" :key="improvement" 
                    class="flex items-start text-sm text-gray-700">
                  <span class="text-orange-500 mr-2">•</span>
                  {{ improvement }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Overall Recommendation -->
          <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
            <h5 class="font-medium text-gray-900 mb-2 flex items-center">
              <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
              </svg>
              Overall Recommendation
            </h5>
            <div class="flex items-start gap-4">
              <div class="flex-1">
                <p class="text-gray-700">{{ agentData.assessment.recommendation }}</p>
              </div>
              <div class="flex-shrink-0">
                <div :class="getRecommendationClass(agentData.assessment.verdict)" 
                     class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ agentData.assessment.verdict }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Business Insights -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">💡 Business Insights & Recommendations</h3>
      
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <div v-else class="space-y-4">
        <div v-for="insight in businessInsights" :key="insight.id" 
             :class="['p-4 rounded-lg border-l-4', getInsightClass(insight.type)]">
          <div class="flex items-start">
            <div class="mr-3">
              <span v-if="insight.type === 'success'" class="text-green-600">✅</span>
              <span v-else-if="insight.type === 'warning'" class="text-yellow-600">⚠️</span>
              <span v-else-if="insight.type === 'danger'" class="text-red-600">🚨</span>
              <span v-else class="text-blue-600">💡</span>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 mb-1">{{ insight.title }}</h4>
              <p class="text-gray-700 mb-2">{{ insight.description }}</p>
              <p class="text-sm font-medium text-gray-900">Recommended Action: {{ insight.action }}</p>
            </div>
          </div>
        </div>
        
        <div v-if="businessInsights.length === 0" class="text-center py-8 text-gray-500">
          <p>No specific insights available for this period.</p>
          <p class="text-sm mt-1">Check back later or adjust your date range.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

// Reactive data
const loading = ref(false)
const selectedDateRange = ref('last_7_days')
const customDateFrom = ref('')
const customDateTo = ref('')
const agentSort = ref('actions')

const analytics = ref({
  totalActions: 0,
  activeAgents: 0,
  ordersToday: 0,
  avgActionsPerAgent: 0
})

const agents = ref([])
const businessInsights = ref([])
const activityData = ref([])
const actionTypesData = ref([])

// Individual Agent Analysis
const availableAgents = ref([])
const selectedAgentId = ref('')
const selectedAgent = ref(null)
const agentAnalysisPeriod = ref('last_7_days')
const loadingAgentData = ref(false)
const agentData = ref(null)

// Chart refs
const activityChart = ref(null)
const actionTypesChart = ref(null)
let activityChartInstance = null
let actionTypesChartInstance = null

// Agent-specific chart refs
const agentActivityChart = ref(null)
const agentPerformanceChart = ref(null)
const agentResponseChart = ref(null)
let agentActivityChartInstance = null
let agentPerformanceChartInstance = null
let agentResponseChartInstance = null

// Computed properties
const sortedAgents = computed(() => {
  const sorted = [...agents.value]
  if (agentSort.value === 'actions') {
    return sorted.sort((a, b) => b.totalActions - a.totalActions)
  } else {
    return sorted.sort((a, b) => b.successRate - a.successRate)
  }
})

// Methods
const fetchData = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      period: selectedDateRange.value
    })
    
    if (selectedDateRange.value === 'custom') {
      params.append('from', customDateFrom.value)
      params.append('to', customDateTo.value)
    }

    console.log('Fetching analytics data with params:', params.toString())
    
    const response = await fetch(`/api/history/analytics?${params}`)
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Analytics data received:', data)
    
    analytics.value = data.analytics || {}
    agents.value = data.agents || []
    businessInsights.value = data.insights || []
    activityData.value = data.activityTimeline || []
    actionTypesData.value = data.actionTypes || []
    
    console.log('Activity data:', activityData.value)
    console.log('Action types data:', actionTypesData.value)
    
    await nextTick()
    renderCharts()
  } catch (error) {
    console.error('Failed to fetch analytics data:', error)
    // Show some default data for testing
    analytics.value = {
      totalActions: 5,
      activeAgents: 2,
      ordersToday: 1,
      avgActionsPerAgent: 2.5
    }
    activityData.value = [
      { date: 'Sep 22', count: 3 },
      { date: 'Sep 23', count: 2 }
    ]
    actionTypesData.value = [
      { title: 'Product Updated', count: 3 },
      { title: 'Order Created', count: 1 },
      { title: 'Orders Assigned', count: 1 }
    ]
    agents.value = []
    businessInsights.value = []
    
    // Render charts with fallback data
    await nextTick()
    renderCharts()
  } finally {
    loading.value = false
  }
}

const renderCharts = () => {
  // Wait for next tick to ensure DOM is ready
  nextTick(() => {
    console.log('DOM ready, checking canvas elements...')
    console.log('Activity chart ref:', activityChart.value)
    console.log('Action types chart ref:', actionTypesChart.value)
    
    // Only render charts if we have data and canvas elements
    const hasActivityData = activityData.value && activityData.value.length > 0
    const hasActionTypesData = actionTypesData.value && actionTypesData.value.length > 0
    
    if (hasActivityData && activityChart.value) {
      renderActivityChart()
    } else if (hasActivityData) {
      console.warn('Activity data available but canvas not ready, retrying...')
      setTimeout(() => {
        if (activityChart.value) {
          renderActivityChart()
        }
      }, 100)
    }
    
    if (hasActionTypesData && actionTypesChart.value) {
      renderActionTypesChart()
    } else if (hasActionTypesData) {
      console.warn('Action types data available but canvas not ready, retrying...')
      setTimeout(() => {
        if (actionTypesChart.value) {
          renderActionTypesChart()
        }
      }, 100)
    }
  })
}

const renderActivityChart = () => {
  if (activityChartInstance) {
    activityChartInstance.destroy()
    activityChartInstance = null
  }
  
  if (!activityChart.value) {
    console.error('Activity chart canvas not found')
    return
  }
  
  if (!activityData.value || !activityData.value.length) {
    console.warn('No activity data available for chart')
    return
  }
  
  console.log('Rendering activity chart with data:', activityData.value)
  
  const ctx = activityChart.value.getContext('2d')
  activityChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: activityData.value.map(item => item.date),
      datasets: [{
        label: 'Actions',
        data: activityData.value.map(item => item.count),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        },
        x: {
          grid: {
            display: false
          }
        }
      }
    }
  })
  
  console.log('Activity chart rendered successfully')
}

const renderActionTypesChart = () => {
  if (actionTypesChartInstance) {
    actionTypesChartInstance.destroy()
    actionTypesChartInstance = null
  }
  
  if (!actionTypesChart.value) {
    console.error('Action types chart canvas not found')
    return
  }
  
  if (!actionTypesData.value || !actionTypesData.value.length) {
    console.warn('No action types data available for chart')
    return
  }
  
  console.log('Rendering action types chart with data:', actionTypesData.value)
  
  const ctx = actionTypesChart.value.getContext('2d')
  actionTypesChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: actionTypesData.value.map(item => item.title),
      datasets: [{
        data: actionTypesData.value.map(item => item.count),
        backgroundColor: [
          '#3B82F6', // Blue
          '#10B981', // Green
          '#F59E0B', // Yellow
          '#EF4444', // Red
          '#8B5CF6', // Purple
          '#06B6D4', // Cyan
          '#F97316', // Orange
          '#84CC16'  // Lime
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
  
  console.log('Action types chart rendered successfully')
}

const sortAgents = (type) => {
  agentSort.value = type
}

const getSuccessRateClass = (rate) => {
  if (rate >= 80) return 'text-green-600 font-medium'
  if (rate >= 60) return 'text-yellow-600 font-medium'
  return 'text-red-600 font-medium'
}

const getPerformanceStatusClass = (status) => {
  const classes = 'px-2 py-1 rounded-full text-xs font-medium'
  if (status === 'Excellent') return `${classes} bg-green-100 text-green-800`
  if (status === 'Good') return `${classes} bg-blue-100 text-blue-800`
  if (status === 'Average') return `${classes} bg-yellow-100 text-yellow-800`
  return `${classes} bg-red-100 text-red-800`
}

const getInsightClass = (type) => {
  if (type === 'success') return 'bg-green-50 border-green-400'
  if (type === 'warning') return 'bg-yellow-50 border-yellow-400'
  if (type === 'danger') return 'bg-red-50 border-red-400'
  return 'bg-blue-50 border-blue-400'
}

const fetchAvailableAgents = async () => {
  try {
    const response = await fetch('/api/history/agents')
    if (response.ok) {
      const data = await response.json()
      availableAgents.value = data.agents || []
    }
  } catch (error) {
    console.error('Failed to fetch available agents:', error)
    // Fallback data
    availableAgents.value = [
      { id: 1, name: 'Agent Smith' },
      { id: 2, name: 'Agent Johnson' },
      { id: 3, name: 'Agent Brown' }
    ]
  }
}

const fetchAgentData = async () => {
  if (!selectedAgentId.value) return
  
  loadingAgentData.value = true
  selectedAgent.value = availableAgents.value.find(agent => agent.id == selectedAgentId.value)
  
  try {
    const params = new URLSearchParams({
      agent_id: selectedAgentId.value,
      period: agentAnalysisPeriod.value
    })
    
    console.log('Fetching agent data with params:', params.toString())
    
    const response = await fetch(`/api/history/agent-analysis?${params}`)
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Agent data received:', data)
    
    agentData.value = data
    
    await nextTick()
    renderAgentCharts()
    
  } catch (error) {
    console.error('Failed to fetch agent data:', error)
    // Provide sample data
    agentData.value = {
      overview: {
        total_actions: 45,
        orders_handled: 23,
        avg_response_time: '2.5h',
        success_rate: 85
      },
      daily_activity: [
        { date: 'Sep 17', actions: 5 },
        { date: 'Sep 18', actions: 8 },
        { date: 'Sep 19', actions: 6 },
        { date: 'Sep 20', actions: 12 },
        { date: 'Sep 21', actions: 9 },
        { date: 'Sep 22', actions: 3 },
        { date: 'Sep 23', actions: 2 }
      ],
      performance_metrics: [
        { label: 'Order Created', value: 15 },
        { label: 'Order Updated', value: 20 },
        { label: 'Status Changed', value: 10 }
      ],
      response_trend: [
        { date: 'Sep 17', avg_hours: 3.2 },
        { date: 'Sep 18', avg_hours: 2.8 },
        { date: 'Sep 19', avg_hours: 2.1 },
        { date: 'Sep 20', avg_hours: 1.9 },
        { date: 'Sep 21', avg_hours: 2.4 },
        { date: 'Sep 22', avg_hours: 3.1 },
        { date: 'Sep 23', avg_hours: 2.7 }
      ],
      assessment: {
        strengths: [
          'Consistent daily activity with good response times',
          'High success rate in order processing',
          'Excellent order creation efficiency'
        ],
        improvements: [
          'Response time could be more consistent',
          'Weekend activity could be improved'
        ],
        recommendation: 'This agent shows strong performance with excellent order handling skills. Consider providing additional training on time management to reduce response time variability.',
        verdict: 'Keep - High Performer'
      }
    }
    
    await nextTick()
    renderAgentCharts()
    
  } finally {
    loadingAgentData.value = false
  }
}

const renderAgentCharts = () => {
  nextTick(() => {
    console.log('Rendering agent charts...')
    console.log('Agent activity chart ref:', agentActivityChart.value)
    console.log('Agent performance chart ref:', agentPerformanceChart.value)
    console.log('Agent response chart ref:', agentResponseChart.value)
    console.log('Agent data:', agentData.value)
    
    if (agentData.value?.daily_activity?.length > 0 && agentActivityChart.value) {
      renderAgentActivityChart()
    }
    if (agentData.value?.performance_metrics?.length > 0 && agentPerformanceChart.value) {
      renderAgentPerformanceChart()
    }
    if (agentData.value?.response_trend?.length > 0 && agentResponseChart.value) {
      renderAgentResponseChart()
    }
    
    // If canvas elements aren't ready, retry
    if (!agentActivityChart.value || !agentPerformanceChart.value || !agentResponseChart.value) {
      console.warn('Agent chart canvas elements not ready, retrying...')
      setTimeout(() => {
        if (agentData.value?.daily_activity?.length > 0 && agentActivityChart.value) {
          renderAgentActivityChart()
        }
        if (agentData.value?.performance_metrics?.length > 0 && agentPerformanceChart.value) {
          renderAgentPerformanceChart()
        }
        if (agentData.value?.response_trend?.length > 0 && agentResponseChart.value) {
          renderAgentResponseChart()
        }
      }, 200)
    }
  })
}

const renderAgentActivityChart = () => {
  if (agentActivityChartInstance) {
    agentActivityChartInstance.destroy()
    agentActivityChartInstance = null
  }
  
  if (!agentActivityChart.value) {
    console.error('Agent activity chart canvas not found')
    return
  }
  
  if (!agentData.value?.daily_activity?.length) {
    console.warn('No daily activity data available for agent chart')
    return
  }
  
  console.log('Rendering agent activity chart with data:', agentData.value.daily_activity)
  
  const ctx = agentActivityChart.value.getContext('2d')
  agentActivityChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: agentData.value.daily_activity.map(item => item.date),
      datasets: [{
        label: 'Daily Actions',
        data: agentData.value.daily_activity.map(item => item.actions),
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
  
  console.log('Agent activity chart rendered successfully')
}

const renderAgentPerformanceChart = () => {
  if (agentPerformanceChartInstance) {
    agentPerformanceChartInstance.destroy()
    agentPerformanceChartInstance = null
  }
  
  if (!agentPerformanceChart.value) {
    console.error('Agent performance chart canvas not found')
    return
  }
  
  if (!agentData.value?.performance_metrics?.length) {
    console.warn('No performance metrics data available for agent chart')
    return
  }
  
  console.log('Rendering agent performance chart with data:', agentData.value.performance_metrics)
  
  const ctx = agentPerformanceChart.value.getContext('2d')
  agentPerformanceChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: agentData.value.performance_metrics.map(item => item.label),
      datasets: [{
        data: agentData.value.performance_metrics.map(item => item.value),
        backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  })
  
  console.log('Agent performance chart rendered successfully')
}

const renderAgentResponseChart = () => {
  if (agentResponseChartInstance) {
    agentResponseChartInstance.destroy()
    agentResponseChartInstance = null
  }
  
  if (!agentResponseChart.value) {
    console.error('Agent response chart canvas not found')
    return
  }
  
  if (!agentData.value?.response_trend?.length) {
    console.warn('No response trend data available for agent chart')
    return
  }
  
  console.log('Rendering agent response chart with data:', agentData.value.response_trend)
  
  const ctx = agentResponseChart.value.getContext('2d')
  agentResponseChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: agentData.value.response_trend.map(item => item.date),
      datasets: [{
        label: 'Avg Response Time (hours)',
        data: agentData.value.response_trend.map(item => item.avg_hours),
        borderColor: 'rgb(168, 85, 247)',
        backgroundColor: 'rgba(168, 85, 247, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
  
  console.log('Agent response chart rendered successfully')
}

const getRecommendationClass = (verdict) => {
  if (verdict?.toLowerCase().includes('keep')) return 'bg-green-100 text-green-800'
  if (verdict?.toLowerCase().includes('improve')) return 'bg-yellow-100 text-yellow-800'
  if (verdict?.toLowerCase().includes('consider')) return 'bg-orange-100 text-orange-800'
  return 'bg-red-100 text-red-800'
}

// Lifecycle
onMounted(() => {
  console.log('HistoryOverview component mounted')
  console.log('Chart.js available:', typeof Chart !== 'undefined')
  fetchData()
  fetchAvailableAgents()
})
</script>

<style scoped>
/* Custom styles for charts */
canvas {
  max-height: 250px;
}
</style>
