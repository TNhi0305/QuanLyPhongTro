import React from 'react';
import { StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import NotificationsView from '../views/NotificationsView';

export default function NotificationsScreen() {
  const router = useRouter();

  const handleNavigateToInvoice = (invoiceId?: number) => {
    // Chuyển về màn hình chính và chuyển tab hóa đơn
    router.push('/');
  };

  const handleNavigateToMaintenance = (maintenanceId?: number) => {
    router.push('/');
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <View style={styles.container}>
        <NotificationsView
          onNavigateToInvoice={handleNavigateToInvoice}
          onNavigateToMaintenance={handleNavigateToMaintenance}
        />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#FFFFFF'
  },
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC'
  }
});
