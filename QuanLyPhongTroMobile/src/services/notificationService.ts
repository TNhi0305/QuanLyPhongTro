import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

// Configure foreground notification behavior
if (Platform.OS !== 'web') {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowAlert: true,
      shouldPlaySound: true,
      shouldSetBadge: true,
      shouldShowBanner: true,
      shouldShowList: true,
      priority: Notifications.AndroidNotificationPriority?.MAX ?? 3
    }),
  });
}


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
    // 1. Kiểm tra và xin quyền thông báo nếu chưa có
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;
    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }
    if (finalStatus !== 'granted') {
      console.warn('[LocalNotification] Quyền thông báo chưa được cấp bởi người dùng.');
      return null;
    }

    // 2. Đảm bảo Notification Channel tồn tại trên Android với độ ưu tiên MAX
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Thông báo Quản lý phòng trọ',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#208AEF',
        sound: 'default',
        enableLights: true,
        enableVibrate: true,
        showBadge: true
      });
    }

    // 3. Cấu hình trigger: nếu seconds > 0 thì lên lịch, còn 0 thì bắn ngay lập tức
    const delaySeconds = typeof params.seconds === 'number' ? params.seconds : 1;
    const trigger = delaySeconds > 0
      ? {
          type: Notifications.SchedulableTriggerInputTypes.TIME_INTERVAL,
          seconds: delaySeconds,
          repeats: false,
          channelId: 'default'
        }
      : {
          channelId: 'default'
        };

    const id = await Notifications.scheduleNotificationAsync({
      content: {
        title: params.title,
        body: params.body,
        data: params.data || {},
        sound: 'default',
        priority: Notifications.AndroidNotificationPriority?.MAX ?? 3
      },
      trigger: trigger as any
    });
    console.log(`[LocalNotification] Đã lên lịch #${id} sau ${delaySeconds}s`);
    return id;
  } catch (err) {
    console.warn('[LocalNotification] Lỗi lên lịch thông báo:', err);
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
