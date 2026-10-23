import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

// Configure foreground notification behavior
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
    shouldShowBanner: true,
    shouldShowList: true,
    priority: Notifications.AndroidNotificationPriority.MAX
  }),
});


export interface NotificationPayload {
  notification_id?: number | string;
  type?: string;
  reference_type?: string;
  reference_id?: number | string;
  invoice_id?: number | string;
  request_id?: number | string;
  [key: string]: any;
}

/**
 * Register device for push notifications and get Expo Push Token.
 */
export async function registerForPushNotificationsAsync(): Promise<string | null> {
  if (Platform.OS === 'web') {
    return null;
  }

  try {
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Thông báo Quản lý phòng trọ',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#208AEF',
        sound: 'default',
        enableLights: true,
        enableVibrate: true
      });
    }

    if (!Device.isDevice) {
      console.log('[Push] Must use physical device for remote push notifications.');
      // Return a simulated token for simulator demo purposes
      return `ExponentPushToken[SimDevice_${Platform.OS}_${Date.now()}]`;
    }

    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      console.log('[Push] Permission not granted by user.');
      return null;
    }

    const tokenResponse = await Notifications.getExpoPushTokenAsync({
      // projectId is optional in Expo Go, inferred from project configuration
    });

    const token = tokenResponse.data;
    console.log('[Push] Registered Expo Push Token:', token);
    return token;
  } catch (error) {
    console.warn('[Push] Error getting push token:', error);
    return null;
  }
}

/**
 * Schedule a local notification (e.g. Bill payment reminder).
 */
export async function scheduleLocalReminder(params: {
  title: string;
  body: string;
  seconds?: number;
  data?: NotificationPayload;
}): Promise<string | null> {
  if (Platform.OS === 'web') return null;

  try {
    const id = await Notifications.scheduleNotificationAsync({
      content: {
        title: params.title,
        body: params.body,
        data: params.data || {},
        sound: 'default'
      },
      trigger: {
        type: Notifications.SchedulableTriggerInputTypes.TIME_INTERVAL,
        seconds: params.seconds || 5,
        repeats: false
      }
    });
    console.log(`[LocalNotification] Scheduled #${id} in ${params.seconds || 5}s`);
    return id;
  } catch (err) {
    console.warn('[LocalNotification] Error scheduling:', err);
    return null;
  }
}

/**
 * Cancel a scheduled local notification.
 */
export async function cancelScheduledReminder(id: string): Promise<void> {
  if (Platform.OS === 'web') return;
  try {
    await Notifications.cancelScheduledNotificationAsync(id);
  } catch (e) {
    console.warn('Error cancelling notification:', e);
  }
}

/**
 * Check if the app was launched by tapping a notification (cold start / terminated state).
 */
export async function checkInitialNotification(): Promise<NotificationPayload | null> {
  if (Platform.OS === 'web') return null;

  try {
    const response = await Notifications.getLastNotificationResponseAsync();
    if (response) {
      const data = response.notification.request.content.data as NotificationPayload;
      console.log('[Push] App opened from cold start notification:', data);
      return data;
    }
  } catch (err) {
    console.warn('[Push] Error checking initial notification:', err);
  }
  return null;
}
